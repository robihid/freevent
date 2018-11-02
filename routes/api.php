<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1'], function() {
  Route::resource('/events', 'EventsController', [
    'except' => ['create', 'edit']
  ]);

  Route::resource('/events/registration', 'RegistrationController', [
    'only' => ['store', 'destroy']
  ]);

  Route::resource('/events/save', 'SaveController', [
    'only' => ['store', 'destroy']
  ]);

  Route::resource('/tickets', 'TicketsController', [
    'only' => ['index', 'show']
  ]);

  Route::resource('/wishlist', 'WishlistController', [
    'only' => ['index', 'show']
  ]);

  Route::post('/user/register', [
    'uses' => 'AuthController@register'
  ]);

  Route::post('/user/login', [
    'uses' => 'AuthController@login'
  ]);
});
