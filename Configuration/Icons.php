<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'cooler-filelist' => [
        'provider' => SvgIconProvider::class,
        // The source bitmap file
        'source' => 'EXT:file_dashboard/Resources/Public/Icons/Extension.svg',
        // All icon providers provide the possibility to register an icon that spins
        'spinning' => false,
    ],
];
