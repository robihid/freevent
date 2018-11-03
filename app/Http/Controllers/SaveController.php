<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

use App\Event;
use App\User;

class SaveController extends Controller
{
  public function __construct() {
    $this->middleware('jwt.auth');
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request, $event_id)
  {
    $user = JWTAuth::toUser($request->header('token'));
    $user_id = $user->id;

    $event = Event::findOrFail($event_id);
    $user = User::findOrFail($user_id);

    $response = [
      'msg' => 'Event ini sudah berada di wishlist anda',
      'user' => $user,
      'event' => $event,
      'unregister' => [
        'href' => 'api/v1/events/' . $event->id . '/save',
        'method' => 'DELETE'
      ]
    ];

    // Jika user telah registrasi ke event
    if ($event->wishlist()->where('users.id', $user->id)->first()) {
      return response()->json($response, 404);
    }

    $user->wishlist()->attach($event);

    $response = [
      'msg' => 'Event berhasil ditambahkan ke wishlist',
      'user' => $user,
      'event' => $event,
      'unsave' => [
        'href' => 'api/v1/events/' . $event->id . '/save',
        'method' => 'DELETE'
      ]
    ];

    return response()->json($response, 201);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy(Request $request, $event_id)
  {
    $user = JWTAuth::toUser($request->header('token'));

    $event = Event::findOrFail($event_id);
    $row = DB::table('wishlist')->where([['event_id', $event_id], ['user_id', $user->id]])->delete();

    $response = [
      'msg' => 'Event berhasil dihapus dari wishlist',
      'event' => $event,
      'user' => $user,
      'save' => [
        'href' => 'api/v1/events/'. $event_id .'/save',
        'method' => 'POST',
        'params' => 'event_id, user_id'
      ]
    ];

    return response()->json($response, 200);
  }
}
