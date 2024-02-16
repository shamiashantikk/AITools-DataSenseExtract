<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\validationController;
use App\Http\Controllers\glareController;
use App\Http\Controllers\HumanDetectionController;

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
Route::post('/upload', [validationController::class, 'uploadImage'])->name('upload');;
Route::view('/eye-detection', 'eye-detection');

//Route::post('/check-glare', [gladeController::class, 'checkGlare']);

Route::post('/detect-human', [HumanDetectionController::class, 'detectHuman']);
Route::get('/uploadimage', [HumanDetectionController::class, 'showUploadForm']);
