<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    protected $fillable = [
        'name',
        'email',
        'event_id',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
