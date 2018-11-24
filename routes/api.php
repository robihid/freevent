<?php

use Illuminate\Http\Request;

Route::group(['middleware' => 'cors'], function() {

  // Route untuk register
  Route::post('/user/register', [
    'uses' => 'AuthController@register'
  ]);

  // Route untuk login
  Route::post('/user/login', [
    'uses' => 'AuthController@login'
  ]);

  // Route untuk events
  Route::resource('/events', 'EventsController', [
    'except' => ['create', 'edit', 'update', 'destroy']
  ]);

  // Route untuk tickets
  Route::resource('/tickets', 'TicketsController', [
    'only' => ['index', 'show', 'store']
  ]);

  // Route untuk wishlist
  Route::resource('/wishlist', 'WishlistController', [
    'only' => ['index', 'store', 'destroy']
  ]);

  // Route untuk categories
  Route::resource('/categories', 'CategoriesController', [
    'only' => ['index', 'store']
  ]);
});
