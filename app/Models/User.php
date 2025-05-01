<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedResponses()
    {
        return $this->hasMany(AlertResponse::class, 'user_id');
    }

    public function responderTeams()
    {
        return $this->belongsToMany(Responder::class, 'user_responder')
                ->withPivot('role');
    }

    protected function coordinates(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'lat' => DB::selectOne("SELECT ST_Y(coordinates) as lat FROM users WHERE id = ?", [$this->id])->lat,
                'lng' => DB::selectOne("SELECT ST_X(coordinates) as lng FROM users WHERE id = ?", [$this->id])->lng
            ],
            set: fn (array $value) => DB::raw("ST_GeomFromText('POINT({$value['lng']} {$value['lat']})', 4326)")
        );
    }

    public function scopeWithinGeometryRadius($query, $point, $radius)
    {
        return $query->whereRaw(
            "ST_Distance_Sphere(
                coordinates, 
                ST_GeomFromText(?, 4326)
            ) <= ?",
            [$point->toWkt(), $radius * 1000]
        );
    }
}
