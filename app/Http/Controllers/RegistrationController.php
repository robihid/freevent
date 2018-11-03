<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

use App\Event;
use App\User;

class RegistrationController extends Controller
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
          'msg' => 'Anda sudah terdaftar pada event ini',
          'user' => $user,
          'event' => $event,
          'unregister' => [
            'href' => 'api/v1/events/' . $event->id . '/registration',
            'method' => 'DELETE'
          ]
        ];

        // Jika user telah registrasi ke event
        if ($event->users()->where('users.id', $user->id)->first()) {
          return response()->json($response, 404);
        }

        $user->events()->attach($event);

        $response = [
          'msg' => 'Pendaftaran berhasil',
          'user' => $user,
          'event' => $event,
          'unregister' => [
            'href' => 'api/v1/events/' . $event->id . '/registration',
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
      $row = DB::table('tickets')->where([['event_id', $event_id], ['user_id', $user->id]])->delete();

      $response = [
        'msg' => 'Anda berhasil melakukan pembatalan',
        'event' => $event,
        'user' => $user,
        'register' => [
          'href' => 'api/v1/events/registration',
          'method' => 'POST',
          'params' => 'event_id, user_id'
        ]
      ];

      return response()->json($response, 200);
    }
}
