<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\ProfileController;


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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', function (){
    return 'Hello, this is a test route!';
});

Route::get('/blog', [DemoController::class, 'showMessage']);

Route::get('/myprofile', function () {
    return view('myprofile');
});

Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');



