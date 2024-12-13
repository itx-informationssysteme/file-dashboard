<?php

namespace Itx\FileDashboard\EventListener;

use TYPO3\CMS\Core\Resource\Event\AfterFileAddedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileDeletedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileMovedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileRenamedEvent;
use TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class FileChangedListener
{
    public function onFileAdded(AfterFileAddedEvent $event): void
    {
        $this->clearCache();
    }

    public function onFileDeleted(AfterFileDeletedEvent $event): void
    {
        $this->clearCache();
    }

    public function onFileMoved(AfterFileMovedEvent $event): void
    {
        $this->clearCache();
    }

    public function onFileRenamed(AfterFileRenamedEvent $event): void
    {
        $this->clearCache();
    }

    public function onFileReplaced(AfterFileReplacedEvent $event): void
    {
        $this->clearCache();
    }

    protected function clearCache()
    {
        $container = GeneralUtility::getContainer();

        /** @var FrontendInterface $cache */
        $cache = $container->get('cache.file_dashboard_cache');
        $cache->flushByTag('file_dashboard');
    }
}
