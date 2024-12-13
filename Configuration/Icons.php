<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;

return [
    'cooler-filelist' => [
        'provider' => BitmapIconProvider::class,
        // The source bitmap file
        'source' => 'EXT:file_dashboard/Resources/Public/Icons/Extension.png',
        // All icon providers provide the possibility to register an icon that spins
        'spinning' => true,
    ],
];
