<?php

namespace App\Http\Controllers;
use App\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller {
	public function index() {
		$categories = Category::all();
		return $categories;
	}

	public function store(Request $request) {
		$this->validate($request, [
			'name' => 'required'
		]);

		$category = new Category([
			'name' => $request->input('name')
		]);

		$category->save();

		return $category;
	}
}
