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

Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function() {
  Route::resource('/events', 'EventsController', [
    'except' => ['create', 'edit']
  ]);

  Route::post('/events/{event_id}/registration', [
    'uses' => 'RegistrationController@store'
  ]);

  Route::delete('/events/{event_id}/registration', [
    'uses' => 'RegistrationController@destroy'
  ]);

  Route::post('/events/{event_id}/save', [
    'uses' => 'SaveController@store'
  ]);

  Route::delete('/events/{event_id}/save', [
    'uses' => 'SaveController@destroy'
  ]);

  Route::resource('/tickets', 'TicketsController', [
    'only' => ['index']
  ]);

  Route::resource('/wishlist', 'WishlistController', [
    'only' => ['index']
  ]);

  Route::post('/user/register', [
    'uses' => 'AuthController@register'
  ]);

  Route::post('/user/login', [
    'uses' => 'AuthController@login'
  ]);
});
