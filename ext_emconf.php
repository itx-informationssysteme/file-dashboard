<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/* @phpstan-ignore-next-line */
$EM_CONF[$_EXTKEY] = [
    'title' => 'File Dashboard',
    'description' => 'Filterable file list backend module including easy download functionality.',
    'category' => 'backend',
    'author' => '',
    'author_company' => 'it.x informationssysteme gmbh',
    'author_email' => 'typo-itx@itx.de',
    'state' => 'stable',
    'uploadfolder' => 1,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'version' => '2.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.1-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
