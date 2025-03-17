<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;

Route::middleware('api')->group(function () {
    Route::get('jobsByfilter', [JobController::class, 'getDataSimple']);
    Route::get('jobsByService', [JobController::class, 'getDataByService']);
});