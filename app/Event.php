<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['title', 'description', 'quota', 'city', 'location', 'start_time', 'end_time', 'image_url', 'organizer_id'];

    public function users() {
      return $this->belongsToMany(User::class, 'tickets');
    }
}
