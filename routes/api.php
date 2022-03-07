<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//Route::group(['middleware' => ['jwt.verify']], function() {
Route::get('/devices/removeaccess', 'UserController@removeAllAccess'); // Response "OK"
Route::get('/notes/getShared/{id}', "NotesController@getShared");
Route::post('/notes/sync', 'NotesController@sync');
Route::get('/devices', 'UserController@listAccess');
//});
Route::post('/authenticate', 'UserController@login');
