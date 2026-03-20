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

//Auth
Route::middleware('throttle:5,1')
   ->post('login', [AuthController::class, 'login']);

//Admin Route
Route::middleware(['auth:sanctum', 'permission:manage users'])
   ->post('/createuser', [ManageUserController::class, 'store']);


   //News
Route::middleware(['auth:sanctum', 'permission:manage news'])
   ->post('/create-news', [NewsController::class, 'store']);
Route::get('/news',[NewsController::class,'index']);
Route::get('/news/{$slug}',[NewsController::class, 'show']);
   //Sertifikasi
Route::middleware(['auth:sanctum', 'permission:manage sertifikasi'])
   ->post('/create-sertifikasi', [SertifikasiController::class, 'store']);
Route::get('/sertifikasi',[SertifikasiController::class,'index']);
Route::get('/sertifikasi/{$slug}',[SertifikasiController::class, 'show']);

   //Kurikulum
Route::middleware(['auth:sanctum', 'permission:manage kurikulum'])
   ->post('/create-kurikulum', [KurikulumController::class, 'store']);
Route::get('/kurikulum',[KurikulumController::class,'index']);
Route::get('/kurikulum/{$slug}',[KurikulumController::class, 'show']);   