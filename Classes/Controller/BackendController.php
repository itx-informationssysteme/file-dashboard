<?php

namespace Itx\FileDashboard\Controller;

use DateTime;
use Exception;
use Itx\FileDashboard\Domain\Repository\FileRepository;
use Itx\FileDashboard\Event\FileRenameEvent;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use ZipStream\ZipStream;

class BackendController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected FileRepository $fileRepository;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        FileRepository $fileRepository,
        private readonly ResourceFactory $resourceFactory,
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->fileRepository = $fileRepository;
    }

    // Lists all files
    public function listAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $arguments = $this->request->getArguments();
        $startTime = new DateTime();
        $endTime = new DateTime();

        if (isset($arguments['dateStart']) xor isset($arguments['dateStop'])) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                LocalizationUtility::translate('LLL:EXT:file_dashboard/Resources/Private/Language/locallang.xlf:warning.setBoth'),
                LocalizationUtility::translate('LLL:EXT:file_dashboard/Resources/Private/Language/locallang.xlf:warning.dateTime'),
                FlashMessage::WARNING,
                true
            );
        } elseif ((isset($arguments['dateStart']) && isset($arguments['dateStop']))) {
            if (($arguments['dateStart'] > $arguments['dateStop'])) {
                $message = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    LocalizationUtility::translate('LLL:EXT:file_dashboard/Resources/Private/Language/locallang.xlf:warning.startAfterEnd'),
                    LocalizationUtility::translate('LLL:EXT:file_dashboard/Resources/Private/Language/locallang.xlf:warning.dateTime'),
                    FlashMessage::WARNING,
                    true
                );
            } elseif ($arguments['dateStart'] == '' || $arguments['dateStop'] == '') {
                $message = GeneralUtility::makeInstance(
                    FlashMessage::class,
                    LocalizationUtility::translate('LLL:EXT:file_dashboard/Resources/Private/Language/locallang.xlf:warning.setBoth'),
                    LocalizationUtility::translate('LLL:EXT:file_dashboard/Resources/Private/Language/locallang.xlf:warning.dateTime'),
                    FlashMessage::WARNING,
                    true
                );
            } else {
                $arguments['queryForDate'] = true;
            }
        }

        $result = $this->fileRepository->getCachedFiles($arguments);

        $files = $result['files'];
        $earliestDate = $result['earliestDate'];
        $latestDate = $result['latestDate'];
        $fileTypes = $result['fileTypes'];

        $maxListItems = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class)
        ->get('file_dashboard')['maximumListItems'];
        $itemsPerPage = $maxListItems != '' && $maxListItems != null && $maxListItems != 0 ? abs($maxListItems) : count($files);
        $page = $this->request->hasArgument('currentPageNumber') ? (int)$arguments['currentPageNumber'] : 1;
        $paginator = new ArrayPaginator($files, $page, $itemsPerPage);
        $pagination = new SimplePagination($paginator);
        $incrementedPageNumber = $page >= $paginator->getNumberOfPages() ? $page : $page + 1;
        $decrementedPageNumber = $page <= 1 ? $page : $page - 1;

        if (!array_key_exists('name', $arguments)) {
            $startTime->setTimestamp($earliestDate);
            $endTime->setTimestamp($latestDate + 60);
            $startTime = $startTime->format('Y-m-d\TH:i');
            $endTime = $endTime->format('Y-m-d\TH:i');
        } else {
            $startTime = $arguments['dateStart'];
            $endTime = $arguments['dateStop'];
        }

        if (isset($message)) {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);
        }

        $this->view->assignMultiple([
            'files' => $this->fileRepository->getCachedFiles([])['files'],
            'totalFiles' => count($files),
            'itemsPerPage' => $itemsPerPage,
            'page' => $page,
            'incrementedPageNumber' => $incrementedPageNumber,
            'decrementedPageNumber' => $decrementedPageNumber,
            'numberOfPages' => $paginator->getNumberOfPages(),
            'paginator' => $paginator,
            'pagination' => $pagination,
            'fileTypes' => $fileTypes,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'args' => $arguments,
        ]);
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    // Downloads single file
    public function downloadAction(): Response
    {
        $file = $this->request->getArguments()['file'];
        $identifier = $file['identifier'];

        try {
            $data = $this->resourceFactory->getFileObject($file['uid']);
        } catch (Exception $e) {
            throw new RuntimeException($e);
        }

        $absolutePath = $data->getForLocalProcessing();

        if (!file_exists($absolutePath)) {
            $name = $file['name'];
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                "File $name doesn't exist",
                'File Error',
                FlashMessage::ERROR,
                true
            );
        }

        $event = $this->eventDispatcher->dispatch(
            new FileRenameEvent($data->getName(), $identifier),
        );
        $name = $event->getFileName();

        $response = new Response();

        $response = $response
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', $file['mime_type'])
            ->withHeader('Content-Disposition', 'attachment; filename="' . $name . '"')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withHeader('Content-Length', (string)filesize($absolutePath));

        if (isset($message)) {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);
        } else {
            $response->getBody()->write(file_get_contents($absolutePath));

            return $response;
        }
        return $this->redirect('list');
    }

    // Archives multiple files into .zip file and then starts download
    public function multiDownloadAction(): Response
    {
        // Set 30 minute timeout for this request
        set_time_limit($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['file_dashboard']['downloadTimeout'] ?? 300);

        $filesToDownload = json_decode($this->request->getArguments()['downloadCheckboxJson'] ?? '{}', true);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="files.zip"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        $zip = new ZipStream(outputName: 'files.zip');

        foreach ($filesToDownload as $key => $identifier) {
            try {
                $file = $this->resourceFactory->getFileObject($key);
            } catch (\Exception $e) {
                continue;
            }

            $absolutePath = $file->getForLocalProcessing();
            $fileName = $file->getName();

            if (!file_exists($absolutePath)) {
                continue;
            }

            $event = $this->eventDispatcher->dispatch(new FileRenameEvent($fileName, $identifier));
            $changedName = $event->getFileName();

            $maxFileNameLength = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['file_dashboard']['maxFileNameLength'] ?? 150;
            if ($maxFileNameLength < 6) {
                $maxFileNameLength = 6;
            } else if ($maxFileNameLength > 230) {
                $maxFileNameLength = 230;
            }
            
            if (strlen($changedName) > $maxFileNameLength) {
                $cutoffFactor = floatval($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['file_dashboard']['cutoffFactor'] ?? 70.0);
                $cutoffFactor /= 100;
                if ($cutoffFactor > 1.0) {
                    $cutoffFactor = 1.0;
                } else if ($cutoffFactor < 0.0) {
                    $cutoffFactor = 0.0;
                }
                $fileExtension = pathinfo($changedName, PATHINFO_EXTENSION);
                $fileExtensionLength = strlen($fileExtension);
                $firstHalf = substr($changedName, 0, round($cutoffFactor * $maxFileNameLength));
                $secondHalf = substr($changedName, round((-(1-$cutoffFactor) * $maxFileNameLength)-$fileExtensionLength), round((1-$cutoffFactor) * $maxFileNameLength));

                $changedName = $firstHalf . '...' . $secondHalf . '.' . pathinfo($changedName, PATHINFO_EXTENSION);
            }

            $zip->addFileFromPath($changedName, $absolutePath);
        }

        $zip->finish();

        exit;
    }

    public function detailAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $file = $this->request->getArguments()['file'];
        $metaData = [];

        $result = $this->fileRepository->getMetaData($file['uid']);

        while ($row = $result->fetchAssociative()) {
            array_push($metaData, $row);
        }

        $this->view->assignMultiple([
            'file' => $file,
            'metaData' => $metaData,
            'args' => $this->request->getArguments()['args'] ?? [],
        ]);
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
