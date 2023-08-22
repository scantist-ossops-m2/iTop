<?php

use Combodo\iTop\DI\ITopKernel;
use Symfony\Component\HttpFoundation\Request;

require_once('approot.inc.php');
require_once(APPROOT.'/application/application.inc.php');
require_once(APPROOT.'/application/startup.inc.php');

$kernel = new ITopKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
