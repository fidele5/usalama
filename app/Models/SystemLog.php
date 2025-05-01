<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = ['user_id', 'event', 'details', 'ip_address', 'user_agent'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
