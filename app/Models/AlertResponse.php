<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlertResponse extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'alert_id', 'responder_id', 'user_id', 
        'action_taken', 'status', 'started_at', 'completed_at'
    ];
    
    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }
    
    public function responder()
    {
        return $this->belongsTo(Responder::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
