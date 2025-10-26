<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('track.requests')->group(function () {
    Route::get('/user', function (Request $request) {
        return 'hello world';
    });
});
