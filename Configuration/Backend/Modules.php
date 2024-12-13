<?php

return [
    'file_dashboard_backend' => [
        'extensionName' => 'FileDashboard',
        'parent' => 'file',
        'tx_file_dashboard',
        'position' => 'default',
        'path' => '/module/filedashboard',
        'controllerActions' => [
            \Itx\FileDashboard\Controller\BackendController::class => [
                'list', 
                'download', 
                'multiDownload', 
                'detail'
            ]
        ],
        'access' => 'user,group',
        'iconIdentifier' => 'cooler-filelist',
        'labels' => 'LLL:EXT:file_dashboard/Resources/Private/Language/locallang_backend.xlf',
        'navigationComponentId' => '',
        'inheritNavigationComponentFromMainModule' => false,
    ]
];