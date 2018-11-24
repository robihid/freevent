<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use JWTAuth;

use JWTAuthException;

class AuthController extends Controller {
	public function register(Request $request) {
		// Validasi Request
		$this->validate($request, [
			'name' => 'required',
			'email' => 'required|email',
			'password' => 'required|min:5',
		]);

		// Buat variabel untuk menyimpan request
		$name = $request->input('name');
		$email = $request->input('email');
		$password = $request->input('password');

		// Buat object user
		$user = new User([
			'name' => $name,
			'email' => $email,
			'password' => bcrypt($password),
		]);

		// Buat credentials untuk JWT
		$credentials = [
			'email' => $email,
			'password' => $password,
		];

		// Menyimpan ke database sekaligus mengirim response
		if ($user->save()) {

			// Pembuatan token
			$token = null;
			try {
				if (!$token = JWTAuth::attempt($credentials)) {
					return response()->json([
						'msg' => 'Email or password are incorrect',
					], 404);
				}
			} catch (JWTAuthException $e) {
				return response()->json([
					'msg' => 'failed to create token',
				], 404);
			}

			$response = [
				'msg' => 'User berhasil dibuat',
				'user' => $user,
				'token' => $token,
			];
			return response()->json($response, 201);
		}

		// Jika data tidak berhasil disimpan
		$response = [
			'msg' => 'Terjadi error',
		];

		return response()->json($response, 404);
	}

	public function login(Request $request) {
		$this->validate($request, [
			'email' => 'required|email',
			'password' => 'required|min:5',
		]);

		$email = $request->input('email');
		$password = $request->input('password');

		$credentials = [
			'email' => $email,
			'password' => $password,
		];

		if ($user = User::where('email', $email)->first()) {
			$token = null;
			try {
				if (!$token = JWTAuth::attempt($credentials)) {
					return response()->json([
						'msg' => 'Email atau password salah',
					], 404);
				}
			} catch (JWTAuthException $e) {
				return response()->json([
					'msg' => 'Gagal membuat token',
				], 404);
			}

			$response = [
				'msg' => 'User berhasil login',
				'user' => $user,
				'token' => $token,
			];

			return response()->json($response, 201);
		}

		$response = [
			'msg' => 'Email tidak ditemukan',
		];

		return response()->json($response, 404);
	}
}
