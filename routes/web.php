<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/cash-detection-webcam', function () {
    return view('webcam-cash-detection');
})->name('cash-detection-webcam');
