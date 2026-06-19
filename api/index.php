<?php

//Mengarahkan ke file autoload vendor bawaan Laravel
require __DIR__ . '/../vendor/autoload.php';

//Menyalakan aplikasi Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

//emproses request yang masuk melalui server built-in PHP Vercel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);