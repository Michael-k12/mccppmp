<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../laravel_project/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_project/bootstrap/app.php';


// Bootstrap Laravel and handle the request...
/** @var Application $app */

$app->handleRequest(Request::capture());
