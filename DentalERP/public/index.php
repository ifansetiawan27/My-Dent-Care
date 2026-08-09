<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

define('LARAVEL_START', microtime(true));

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(
    Illuminate\Http\Request::capture(),
);
