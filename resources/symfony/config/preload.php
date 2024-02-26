<?php

use Combodo\iTop\Application\Helper\Session;

// compute app kernel prod container
$sEnv =  Session::Get('itop_env', 'production');
$sAppKernelProdContainer = APPROOT . "/data/cache-$sEnv/symfony/App_KernelProdContainer.preload.php";

if (file_exists($sAppKernelProdContainer)) {
    require $sAppKernelProdContainer;
}
