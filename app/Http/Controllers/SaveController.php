<?php

namespace App\Http\Controllers;

use App\Event;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class SaveController extends Controller {
	// public function __construct() {
	// 	$this->middleware('jwt.auth');
	// }

	public function store(Request $request, $event_id) {
		// $user = JWTAuth::toUser($request->header('token'));			--AUTENTIKASI DIHAPUS SEMENTARA, UNTUK DEVELOPMENT
		$user = User::find($request->input('user_id'));

		$user_id = $user->id;

		$event = Event::findOrFail($event_id);
		$user = User::findOrFail($user_id);

		$response = [
			'msg' => 'Event ini sudah berada di wishlist anda',
			'user' => $user,
			'event' => $event,
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
		];

		return response()->json($response, 201);
	}

	public function destroy(Request $request, $event_id) {
		// $user = JWTAuth::toUser($request->header('token'));			--AUTENTIKASI DIHAPUS SEMENTARA, UNTUK DEVELOPMENT
		$user = User::find($request->input('user_id'));

		$event = Event::findOrFail($event_id);
		$row = DB::table('wishlist')->where([['event_id', $event_id], ['user_id', $user->id]])->delete();

		$response = [
			'msg' => 'Event berhasil dihapus dari wishlist',
			'event' => $event,
			'user' => $user,
		];

		return response()->json($response, 200);
	}
}
