<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name', 'Product Management REST API'),
        'status' => 'online',
        'api_version' => '1.0.0',
        'postman_collection' => 'docs/postman_collection.json',
    ]);
});
