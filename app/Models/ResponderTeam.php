<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResponderTeam extends Model
{
    use SoftDeletes;
    protected $fillable = ['responder_id', 'name'];
}
