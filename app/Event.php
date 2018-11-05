<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model {
	protected $fillable = ['title', 'description', 'quota', 'city', 'location', 'start_time', 'end_time', 'image_url', 'organizer_id'];

	public function categories() {
		return $this->belongsToMany(Category::class);
	}

	public function users() {
		return $this->belongsToMany(User::class, 'tickets');
	}

	public function wishlist() {
		return $this->belongsToMany(User::class, 'wishlist');
	}
}
