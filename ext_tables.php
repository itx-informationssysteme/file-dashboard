<?php

use Itx\FileDashboard\Hook\DataHandlerHooks;

call_user_func(
    function () {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'FileDashboard',
            'file',
            'tx_file_dashboard',
            'default',
            [
                \Itx\FileDashboard\Controller\BackendController::class => 'list, download, multiDownload, detail',
            ],
            [
                'access' => 'user,group',
                'iconIdentifier' => 'module-version',
                'labels' => 'LLL:EXT:file_dashboard/Resources/Private/Language/locallang_backend.xlf',
                'navigationComponentId' => '',
                'inheritNavigationComponentFromMainModule' => false,
            ]
        );
    }
);

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = DataHandlerHooks::class . '->postProcessClearCache';
    }
);
