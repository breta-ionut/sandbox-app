<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

$debug = (bool) ($_SERVER['APP_DEBUG'] ?? true);
if ($debug) {
    Debug::enable();
}

$request = Request::createFromGlobals();
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', $debug);
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
