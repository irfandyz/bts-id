<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('docs');
});

Route::get('/docs', function () {
    return view('docs');
});

Route::get('/api/documentation', function () {
    return view('docs');
});
