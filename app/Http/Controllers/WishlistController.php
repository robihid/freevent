<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class WishlistController extends Controller {
	public function __construct() {
		$this->middleware('jwt.auth', ['except' => ['store']]);
	}

	public function index(Request $request) {
		$user = JWTAuth::toUser($request->input('token'));
		// $user_id = $request->input('user_id');

		$event_ids = DB::table('wishlist')->where('user_id', $user->id)->pluck('event_id');

		$events = DB::table('events')->whereIn('id', $event_ids)->get();

		foreach ($events as $event) {
			$wishlist_id = DB::table('wishlist')->where([
				['event_id', '=', $event->id],
				['user_id', '=', $user->id]
			])->value('id');
			$event->wishlist_id = $wishlist_id;
			$category_ids = DB::table('category_event')->where('event_id', $event->id)->pluck('category_id');
			$category_names = [];
			foreach ($category_ids as $id) {
				$category_names[] = DB::table('categories')->where('id', $id)->value('name');
			}
			$event->categories = $category_names;
		}

		$response = [
			'msg' => 'Event yang ada di wishlist anda',
			'events' => $events,
		];

		return response()->json($response, 200);
	}

	public function store(Request $request) {
		$user = JWTAuth::toUser($request->input('token'));

		// Validasi request
		$this->validate($request, [
			'event_id' => 'required'
		]);

		$user_id = $user->id;
		$event_id = $request->input('event_id');

		// Menyimpan wishlist (user_id, event_id) ke database
		DB::table('wishlist')->insert(
			['user_id' => $user_id, 'event_id' => $event_id, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
		);

		// Mengambil wishlist dari database untuk ditampilkan sebagai response
		$wishlist = DB::table('wishlist')->where([
			['user_id', '=', $user_id],
			['event_id', '=', $event_id]
		])->get();

		// Membuat array response
		$response = [
			'msg' => 'Wishlist berhasil disimpan',
			'wishlist' => $wishlist
		];

		return response()->json($response, 201);
	}

	public function destroy(Request $request, $event_id) {
		$user = JWTAuth::toUser($request->input('token'));
		// $user = User::find($request->input('user_id'));

		$event = Event::findOrFail($event_id);
		$row = DB::table('wishlist')->where([['event_id', $event_id], ['user_id', $user->id]])->delete();

		$response = [
			'msg' => 'Event berhasil dihapus dari wishlist',
			'event' => $event,
			'user' => $user,
		];

		return response()->json($response, 200);
	}

	// Menampilkan semua data pada tabel wishlist
	// public function getAll() {
	// 	return DB::table('wishlist')->get();
	// }
}
