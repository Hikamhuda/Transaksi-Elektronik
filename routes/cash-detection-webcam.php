<?php

use Illuminate\Support\Facades\Route;

// ...existing code...

Route::get('/cash-detection-webcam', function () {
    return view('webcam-cash-detection');
})->name('cash-detection-webcam');
