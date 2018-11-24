<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use App\User;

class EventsController extends Controller {
	public function __construct() {																	
		$this->middleware('jwt.auth', ['except' => ['index', 'show']]);
	}

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
		$user = JWTAuth::toUser($request->input('token'));		

		$this->validate($request, [
			'title' => 'required',
			'description' => 'required',
			'quota' => 'required',
			'city' => 'required',
			'location' => 'required',
			'start_time' => 'required',
			'end_time' => 'required',
			'image' => 'image|required|max:1999',
		]);

		// Get filename with the extension
		$filenameWithExt = $request->file('image')->getClientOriginalName();
		// Get just filename
		$filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
		// Get just ext
		$extension = $request->file('image')->getClientOriginalExtension();
		// Filename to store
		$fileNameToStore = $filename . '_' . time() . '.' . $extension;
		// Upload Image
		$path = $request->file('image')->storeAs('public/image', $fileNameToStore);

		$title = $request->input('title');
		$categories = $request->input('categories');
		$description = $request->input('description');
		$quota = $request->input('quota');
		$city = $request->input('city');
		$location = $request->input('location');
		$start_time = $request->input('start_time');
		$end_time = $request->input('end_time');
		$image_url = $fileNameToStore;
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
					$category_id = DB::table('categories')->where('name', $categories)->value('id');
					$event->categories()->attach($category_id);
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
}
