<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserResponder extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'responder_id', 'role'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function responder()
    {
        return $this->belongsTo(Responder::class);
    }
}
