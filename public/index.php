<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}




// Bootstrap Laravel and handle the request...
/** @var Application $app */

require __DIR__.'/../mccppmp/vendor/autoload.php';
$app = require_once __DIR__.'/../mccppmp/bootstrap/app.php';


$app->handleRequest(Request::capture());
