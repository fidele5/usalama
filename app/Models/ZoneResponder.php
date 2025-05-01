<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoneResponder extends Model
{
    use SoftDeletes;
    protected $fillable = ['zone_id', 'responder_id', 'is_primary'];
    
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
    
    public function responder()
    {
        return $this->belongsTo(Responder::class);
    }
}
