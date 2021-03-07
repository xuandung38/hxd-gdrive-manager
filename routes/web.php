<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

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

Auth::routes();

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


// Files
Route::group([
    'middleware' => ['web'],
    'as' => 'file-manager.',
], function() {
    Route::get('/browser', [FileController::class, 'browser'] )->name('browser');
    Route::get('/discover', [FileController::class, 'discover'])->name('discover');
    Route::post('/mkdir', [FileController::class, 'makeDirectory'])->name('mkdir');
    Route::post('/delete', [FileController::class, 'delete'])->name('delete');

    Route::group(['middleware' => ['file_manager_mimes']], function() {
        Route::post('/single-upload', [FileController::class, 'singleUpload'])->name('single_upload');
        Route::post('/chunk-upload', [FileController::class, 'chunkUpload'])->name('chunk_upload');

    });
});

if (config('file-manager.anonymous_upload')) {
    Route::group([
        'middleware' => [
            'file_manager_cors',
            'file_manager_encrypt_cookies',
            'file_manager_queued_cookies',
            'file_manager_start_session',
            'file_manager_share_session_errors',
            'file_manager_substitute_bindings',
        ],
        'prefix' => 'file-manager',
        'as' => 'file-manager.',
    ], function() {
        Route::options('/anonymous-upload', function () { return response()->json(); });

        Route::group(['middleware' => ['file_manager_mimes']], function() {
            Route::post('/anonymous-upload', [FileController::class, 'anonymousUpload'])->name('anonymous_upload');
        });

    });
}
