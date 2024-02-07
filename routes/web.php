<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\validationController;
use App\Http\Controllers\glareController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [validationController::class, 'showForm']);
Route::post('/upload', [validationController::class, 'uploadImage'])->name('upload');
Route::get('/facerecog', [validationController::class, 'facerecog']);
Route::get('/check', [glareController::class, 'glare']);
Route::post('/check-glare', [glareController::class, 'checkGlare'])->name('check-glare');
Route::get('/eye-result', [glareController::class, 'eyedetection'])->name('eye-result');
//Route::post('/check-glare', [gladeController::class, 'checkGlare']);
