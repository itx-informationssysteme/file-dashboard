<?php

use Itx\FileDashboard\Hook\DataHandlerHooks;

call_user_func(
    function () {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = DataHandlerHooks::class . '->postProcessClearCache';
    }
);
