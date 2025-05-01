<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alert extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'public_id', 'user_id', 'alert_type_id', 'description', 
        'location', 'address', 'status', 'priority'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function alertType() {
        return $this->belongsTo(AlertType::class);
    }

    public function responses()
    {
        return $this->hasMany(AlertResponse::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->public_id = \Str::uuid();
        });
    }
}
