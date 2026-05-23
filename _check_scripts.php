<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate a request
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Get the raw HTML of @livewireScripts
$scripts = Blade::render('@livewireScripts');
echo $scripts;
