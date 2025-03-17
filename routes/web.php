<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('jobsByfilter', [JobController::class, 'getDataSimple']);
Route::get('jobsByService', [JobController::class, 'getDataByService']);
