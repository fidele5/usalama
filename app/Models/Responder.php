<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responder extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type', 'contact_phone'];

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'zone_responder')
                ->withPivot('is_primary');
    }

    public function teamMembers()
    {
        return $this->belongsToMany(User::class, 'user_responder')
                ->withPivot('role');
    }
}
