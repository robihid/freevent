<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use App\Event;

class TicketsController extends Controller {
	// public function __construct() {
	// 	$this->middleware('jwt.auth', ['except' => ['store']]);
	// }

	public function index(Request $request) {
		// $user = JWTAuth::toUser($request->header('token'));
		$user_id = $request->input('user_id');

		$event_ids = DB::table('tickets')->where('user_id', $user_id)->pluck('event_id');

		$events = DB::table('events')->whereIn('id', $event_ids)->get();

		foreach ($events as $event) {
			$ticket_id = DB::table('tickets')->where([
				['event_id', '=', $event->id],
				['user_id', '=', $user_id]
			])->value('id');
			$event->ticket_id = $ticket_id;
			$category_ids = DB::table('category_event')->where('event_id', $event->id)->pluck('category_id');
			$category_names = [];
			foreach ($category_ids as $id) {
				$category_names[] = DB::table('categories')->where('id', $id)->value('name');
			}
			$event->categories = $category_names;
		}

		$response = [
			'msg' => 'Event yang anda ikuti',
			'events' => $events,
		];

		return response()->json($response, 200);
	}

	public function show(Request $request, $event_id) {
		$user = JWTAuth::toUser($request->header('token'));
		$event = Event::find($event_id);
		$category_ids = DB::table('category_event')->where('event_id', $event->id)->pluck('category_id');
		$category_names = [];
		foreach ($category_ids as $id) {
			$category_names[] = DB::table('categories')->where('id', $id)->value('name');
		}
		$event->categories = $category_names;

		$ticket_id = DB::table('tickets')->where([
			['event_id', '=', $event_id],
			['user_id', '=', $user->id]
		])->value('id');

		$response = [
			'msg' => 'Detail informasi tiket',
			'ticket_id' => $ticket_id,
			'event' => $event,
		];
		return response()->json($response, 200);
	}

	public function store(Request $request) {
		// Validasi request
		$this->validate($request, [
			'user_id' => 'required',
			'event_id' => 'required'
		]);

		$user_id = $request->input('user_id');
		$event_id = $request->input('event_id');

		// Menyimpan ticket (user_id, event_id) ke database
		DB::table('tickets')->insert(
			['user_id' => $user_id, 'event_id' => $event_id]
		);

		// Mengambil ticket dari database untuk ditampilkan sebagai response
		$ticket = DB::table('tickets')->where([
			['user_id', '=', $user_id],
			['event_id', '=', $event_id]
		])->get();

		// Membuat array response
		$response = [
			'msg' => 'Tiket berhasil disimpan',
			'ticket' => $ticket
		];

		return response()->json($response, 201);
	}

	// Menampilkan semua data pada tabel tickets
	public function getAll() {
		return DB::table('tickets')->get();
	}
}
