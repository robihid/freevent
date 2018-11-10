<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use App\User;

class EventsController extends Controller {
	// public function __construct() {																	--AUTENTIKASI DIHAPUS SEMENTARA, UNTUK DEVELOPMENT
	// 	$this->middleware('jwt.auth', ['except' => ['index', 'show']]);
	// }

	public function index() {
		$events = Event::all();

		foreach ($events as $event) {
			$category_ids = DB::table('category_event')->where('event_id', $event->id)->pluck('category_id');
			$category_names = [];
			foreach ($category_ids as $id) {
				$category_names[] = DB::table('categories')->where('id', $id)->value('name');
			}
			$event->categories = $category_names;
		}

		$response = [
			'msg' => 'Semua event yang ada',
			'events' => $events,
		];

		return response()->json($response, 200);
	}

	public function store(Request $request) {
		// $user = JWTAuth::toUser($request->header('token'));			--AUTENTIKASI DIHAPUS SEMENTARA, UNTUK DEVELOPMENT
		$user = User::find($request->input('user_id'));

		$this->validate($request, [
			'title' => 'required',
			'description' => 'required',
			'quota' => 'required',
			'city' => 'required',
			'location' => 'required',
			'start_time' => 'required',
			'end_time' => 'required',
			'image_url' => 'required',
		]);

		$title = $request->input('title');
		$categories = $request->input('categories');
		$description = $request->input('description');
		$quota = $request->input('quota');
		$city = $request->input('city');
		$location = $request->input('location');
		$start_time = $request->input('start_time');
		$end_time = $request->input('end_time');
		$image_url = $request->input('image_url');
		$organizer_id = $user->id;

		$event = new Event([
			'title' => $title,
			'description' => $description,
			'quota' => $quota,
			'city' => $city,
			'location' => $location,
			'start_time' => $start_time,
			'end_time' => $end_time,
			'image_url' => $image_url,
			'organizer_id' => $organizer_id,
		]);

		if ($event->save()) {
			if ($categories) {
				foreach ($categories as $name) {
					$category_id = DB::table('categories')->where('name', $name)->value('id');
					$event->categories()->attach($category_id);
				}
			}
			$event->categories = $categories;
			$response = [
				'msg' => 'Event berhasil dibuat',
				'event' => $event,
			];
			return response()->json($response, 201);
		}

		$response = [
			'msg' => 'Terjadi error saat pembuatan event',
		];
		return response()->json($response, 404);
	}

	public function show($id) {
		$event = Event::with('users')->where('id', $id)->firstOrFail();
		$category_ids = DB::table('category_event')->where('event_id', $event->id)->pluck('category_id');
		$category_names = [];
		foreach ($category_ids as $id) {
			$category_names[] = DB::table('categories')->where('id', $id)->value('name');
		}
		$event->categories = $category_names;

		$response = [
			'msg' => 'Detail informasi event',
			'event' => $event,
		];
		return response()->json($response, 200);
	}

	public function update(Request $request, $id) {
		// $user = JWTAuth::toUser($request->header('token'));			--AUTENTIKASI DIHAPUS SEMENTARA, UNTUK DEVELOPMENT
		$user = User::find($request->input('user_id'));

		$this->validate($request, [
			'title' => 'required',
			'description' => 'required',
			'quota' => 'required',
			'city' => 'required',
			'location' => 'required',
			'start_time' => 'required',
			'end_time' => 'required',
			'image_url' => 'required',
		]);

		$title = $request->input('title');
		$categories = $request->input('categories');
		$description = $request->input('description');
		$quota = $request->input('quota');
		$city = $request->input('city');
		$location = $request->input('location');
		$start_time = $request->input('start_time');
		$end_time = $request->input('end_time');
		$image_url = $request->input('image_url');
		$organizer_id = $user->id;

		// Jika event tidak ditemukan
		if (!$event = Event::find($id)) {
			return response()->json([
				'msg' => 'Event tidak ditemukan',
			], 404);
		}

		// Jika user bukan pembuat event
		if ($event->organizer_id != $organizer_id) {
			return response()->json([
				'msg' => 'Anda tidak diizinkan mengubah event ini',
			], 401);
		}

		$event->title = $title;
		$event->description = $description;
		$event->quota = $quota;
		$event->city = $city;
		$event->location = $location;
		$event->start_time = $start_time;
		$event->end_time = $end_time;
		$event->image_url = $image_url;
		$event->organizer_id = $organizer_id;

		// Jika terjadi kesalahan saat update
		if (!$event->update()) {
			return response()->json([
				'msg' => 'Terjadi error saat melakukan perubahan',
			], 404);
		}

		// Menambahkan category_id dan event_id ke tabel category_event
		$event->categories()->detach();
		if ($categories) {
			foreach ($categories as $name) {
				$category_id = DB::table('categories')->where('name', $name)->value('id');
				$event->categories()->attach($category_id);
			}
		}
		$event->categories = $categories;

		$response = [
			'msg' => 'Event berhasil diubah',
			'event' => $event,
		];

		return response()->json($response, 200);
	}

	public function destroy(Request $request, $id) {
		// $user = JWTAuth::toUser($request->header('token'));			--AUTENTIKASI DIHAPUS SEMENTARA, UNTUK DEVELOPMENT
		$user = User::find($request->input('user_id'));

		$organizer_id = $user->id;

		$event = Event::findOrFail($id);
		$users = $event->users;
		$categories = $event->categories;

		// Jika user bukan pembuat event
		if ($event->organizer_id != $organizer_id) {
			return response()->json([
				'msg' => 'Anda tidak diizinkan menghapus event ini',
			], 401);
		}

		// Menghapus data category_event
		$event->categories()->detach();

		// Menghapus data tickets
		$event->users()->detach();

		// Menghapus data wishlist
		$event->wishlist()->detach();

		if (!$event->delete()) {
			foreach ($users as $user) {
				$event->users()->attach($user);
			}
			return response()->json([
				'msg' => 'Gagal menghapus event',
			], 404);
		}

		$response = [
			'msg' => 'Event berhasil dihapus',
			'event' => $event,
		];

		return response()->json($response, 200);
	}
}
