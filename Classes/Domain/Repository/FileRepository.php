<?php

namespace Itx\FileDashboard\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Result;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\Repository;

class FileRepository extends Repository
{
    protected ConnectionPool $connectionPool;
    protected string $cacheId;

    public function __construct(
        ConnectionPool $connectionPool,
        private readonly FrontendInterface $cache,
    ) {
        $this->connectionPool = $connectionPool;
        $this->cacheId = md5('file_dashboard_cache');
    }

    public function getFiles(array $arguments): array
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('sys_file');
        $queryBuilder
            ->select('*')
            ->from('sys_file')
            ->where($queryBuilder->expr()->notLike('identifier', $queryBuilder->createNamedParameter('/typo3conf%', Connection::PARAM_STR)))
            ->andWhere($queryBuilder->expr()->notLike('identifier', $queryBuilder->createNamedParameter('%_temp_%', Connection::PARAM_STR)));

        if (isset($arguments['name']) && $arguments['name'] !== '') {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('name', $queryBuilder->createNamedParameter('%' . $arguments['name'] . '%', Connection::PARAM_STR)));
        }
        if (isset($arguments['path']) && $arguments['path'] !== '') {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->like('identifier', $queryBuilder->createNamedParameter('%' . $arguments['path'] . '%', Connection::PARAM_STR)));
        }
        if (isset($arguments['fileType']) && $arguments['fileType'] !== 'All') {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('extension', $queryBuilder->createNamedParameter($arguments['fileType'], Connection::PARAM_STR)));
        }
        if (array_key_exists('queryForDate', $arguments)) {
            $filterStartTime = DateTime::createFromFormat('Y-m-d\TH:i', $arguments['dateStart'])->getTimestamp();
            $filterEndTime = DateTime::createFromFormat('Y-m-d\TH:i', $arguments['dateStop'])->getTimestamp();
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('creation_date', $filterEndTime))
                ->andWhere($queryBuilder->expr()->gte('creation_date', $filterStartTime));
        }
        $result = $queryBuilder->executeQuery();

        $files = [];
        $earliestDate = time();
        $latestDate = 0;

        while ($row = $result->fetchAssociative()) {
            $files[] = $row;
            if ($row['creation_date'] < $earliestDate && !array_key_exists('name', $arguments)) {
                $earliestDate = $row['creation_date'];
            }
            if ($row['creation_date'] > $latestDate && !array_key_exists('name', $arguments)) {
                $latestDate = $row['creation_date'];
            }
        }

        return ['files' => $files, 'earliestDate' => $earliestDate, 'latestDate' => $latestDate];
    }

    public function getFileExtensions(): array
    {
        $fileTypes = ['All'];

        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('sys_file');
        $queryBuilder
            ->select('extension')
            ->from('sys_file')
            ->distinct();
        $result = $queryBuilder->executeQuery();

        while ($row = $result->fetchAssociative()) {
            if (!in_array($row['extension'], $fileTypes)) {
                array_push($fileTypes, $row['extension']);
            }
        }

        return $fileTypes;
    }

    public function getMetaData(int $uid): Result
    {
        $queryBuilder = $this->connectionPool
            ->getQueryBuilderForTable('sys_file');
        $queryBuilder
            ->select('*')
            ->from('sys_file_metadata')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_STR)));

        return $queryBuilder->executeQuery();
    }

    public function getCachedFiles(array $arguments): array
    {
        if (array_key_exists('name', $arguments) || $arguments == []) {
            $value = $this->getFiles($arguments);
            $value['fileTypes'] = $this->getFileExtensions();
            return $value;
        }

        $value = $this->cache->get($this->cacheId);
        if ($value === false) {
            $value = $this->getFiles($arguments);
            $value['fileTypes'] = $this->getFileExtensions();
            $this->cache->set($this->cacheId, $value, ['file_dashboard'], null);
        }
        return $value;
    }
}
