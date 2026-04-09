<?php

use App\Http\Controllers\admin\ManageUserController;
use App\Http\Controllers\Auth\EmailController as AuthEmailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SertifikasiController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\UPT\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', function(){
   echo("api is running...");
});


//Auth
Route::middleware('throttle:5,1')
   ->post('login', [AuthController::class, 'login']);
Route::middleware('throttle:5,1')
   ->post('logout', [AuthController::class, 'logout']);
Route::middleware(['auth:sanctum', 'throttle:6,1'])
   ->post('/email/verification-notification', function (Request $request) {
      if ($request->user()->hasVerifiedEmail()) {
         return response()->json([
            '{+}' => 'Email already verified!',
         ], 200);
      }
      $request->user()->sendEmailVerificationNotification();
      return response()->json([
         '{+}' => 'Verification sent'
      ], 200);
   });
Route::get('/email/verify/{id}/{hash}', [AuthEmailController::class, 'verify'])
   ->middleware(['signed'])
   ->name('verification.verify');


//Admin Route
Route::middleware(['auth:sanctum', 'permission:manage users'])
   ->post('/users', [ManageUserController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:manage users'])
   ->get('/users',[ManageUserController::class, 'UserList']);
Route::middleware(['auth:sanctum', 'permission:manage users'])
   ->patch('/users/{public_id}', [ManageUserController::class, 'ModifyUser']);
Route::middleware(['auth:sanctum', 'permission:manage users'])
   ->delete('/users/{public_id}',[ManageUserController::class, 'destroy']);

   // PRofile Route
Route::middleware(['auth:sanctum', 'permission:manage profile'])
   ->patch('/update-profile', [ProfileController::class, 'modifyProfile']);
   
   //News
Route::middleware(['auth:sanctum', 'permission:manage news'])
   ->post('/create-news', [NewsController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:manage news'])
   ->patch('/update-news/{slug}', [NewsController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:manage news'])
   ->delete('/delete-news/{slug}', [NewsController::class, 'destroy']);
Route::get('/news',[NewsController::class,'index']);
Route::get('/news/{slug}',[NewsController::class, 'show']);

   //Sertifikasi
Route::middleware(['auth:sanctum', 'permission:manage certificate'])
   ->post('/create-sertifikasi', [SertifikasiController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:manage certificate'])
   ->patch('/update-sertifikasi/{slug}', [SertifikasiController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:manage certificate'])
   ->delete('/delete-sertifikasi/{slug}', [SertifikasiController::class, 'destroy']);
Route::get('/sertifikasi',[SertifikasiController::class,'index']);
Route::get('/sertifikasi/{slug}',[SertifikasiController::class, 'show']);
Route::get('sertifikasi/file/{filename}',[SertifikasiController::class, 'ViewSertifikasiFile']);


   //Kurikulum
Route::middleware(['auth:sanctum', 'permission:manage kurikulum'])
   ->post('/create-kurikulum', [KurikulumController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:manage kurikulum'])
   ->patch('/update-kurikulum/{slug}', [KurikulumController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:manage kurikulum'])
   ->delete('/delete-kurikulum/{slug}', [KurikulumController::class, 'destroy']);
Route::get('/kurikulum',[KurikulumController::class,'index']);
Route::get('/kurikulum/{slug}',[KurikulumController::class, 'show']);
Route::get('kurikulum/file/{filename}',[KurikulumController::class, 'ViewKurikulumFileDeployed']);
// Journal
Route::middleware(['auth:sanctum', 'permission:manage jurnal'])->post('/create-journal', [JournalController::class, 'store']);
Route::middleware(['auth:sanctum', 'permission:manage jurnal'])->patch('/update-journal/{slug}', [JournalController::class, 'update']);
Route::middleware(['auth:sanctum', 'permission:manage jurnal'])->delete('/delete-journal/{slug}', [JournalController::class, 'destroy']);
Route::get('/journal', [JournalController::class, 'index']);
Route::get('/journal/{slug}', [JournalController::class, 'show']);