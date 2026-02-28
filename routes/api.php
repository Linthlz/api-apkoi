<?php

use App\Http\Controllers\admin\ManageUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SertifikasiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('throttle:5,1')
   ->post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'permission:manage users'])
   ->post('/createuser', [ManageUserController::class, 'store']);

Route::middleware(['auth:sanctum', 'permission:manage news'])
   ->post('/create-news', [NewsController::class, 'store']);

Route::middleware(['auth:sanctum', 'permission:manage sertifikasi'])
   ->post('/create-sertifikasi', [SertifikasiController::class, 'store']);

Route::middleware(['auth:sanctum', 'permission:manage kurikulum'])
   ->post('/create-kurikulum', [KurikulumController::class, 'store']);