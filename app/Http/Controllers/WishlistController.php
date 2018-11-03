<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class WishlistController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth');
  }
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $user = JWTAuth::toUser($request->header('token'));

    $event_ids = DB::table('wishlist')->where('user_id', $user->id)->pluck('event_id');

    $events = DB::table('events')->whereIn('id', $event_ids)->get();

    foreach ($events as $event) {
      $category_ids = DB::table('category_event')->where('event_id', $event->id)->pluck('category_id');
      $category_names = [];
      foreach ($category_ids as $id) {
        $category_names[] = DB::table('categories')->where('id', $id)->value('name');
      }
      $event->categories = $category_names;
      $event->view_event = [
        'href' => 'api/v1/events/' . $event->id,
        'method' => 'GET'
      ];
    }

    $response = [
      'msg' => 'Event yang ada di wishlist anda',
      'events' => $events
    ];

    return response()->json($response, 200);
  }
}
