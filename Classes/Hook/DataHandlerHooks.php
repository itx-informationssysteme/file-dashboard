<?php

namespace Itx\FileDashboard\Hook;

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerHooks
{
    public function postProcessClearCache()
    {
        $container = GeneralUtility::getContainer();

        /** @var FrontendInterface $cache */
        $cache = $container->get('cache.file_dashboard_cache');
        $cache->flushByTag('file_dashboard');
    }
}
