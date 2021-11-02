<?php


namespace App\Http\Controllers;
use App\Http\Middleware\RedirectFromOldSlug;
use App\Http\Requests\AppealPostRequest;
use Illuminate\Support\Facades\Route;
use \App\Models\News;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/news', [NewsController::class, 'getList'])->name('news_list');

Route::get('/news/{slug}', [NewsController::class, 'getDetails'])->name('news_item');

Route::get('/appeal', [AppealController::class, 'create'])->name('appeal');
Route::post('/appeal/save', [AppealController::class, 'save'])->name('save_appeal');
