<?php

namespace App\Http\Controllers;

use App\Event;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class RegistrationController extends Controller {
	public function __construct() {
		$this->middleware('jwt.auth');
	}

	public function store(Request $request, $event_id) {
		$user = JWTAuth::toUser($request->header('token'));

		$user_id = $user->id;

		$event = Event::findOrFail($event_id);
		$user = User::findOrFail($user_id);

		$response = [
			'msg' => 'Anda sudah terdaftar pada event ini',
			'user' => $user,
			'event' => $event,
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
		];

		return response()->json($response, 201);
	}

	public function destroy(Request $request, $event_id) {
		$user = JWTAuth::toUser($request->header('token'));

		$event = Event::findOrFail($event_id);
		$row = DB::table('tickets')->where([['event_id', $event_id], ['user_id', $user->id]])->delete();

		$response = [
			'msg' => 'Anda berhasil melakukan pembatalan',
			'event' => $event,
			'user' => $user,
		];

		return response()->json($response, 200);
	}
}
