<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class GeometryCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value) return null;
        
        // Return as WKT (Well-Known Text)
        try {
            $point = DB::selectOne(
                "SELECT ST_X(?) as lng, ST_Y(?) as lat",
                [$value, $value]
            );
            
            return [
                'lat' => (float) $point->lat,
                'lng' => (float) $point->lng
            ];
        } catch (QueryException $e) {
            return null;
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value) return null;
        
        // Accept both WKT or array
        if (is_array($value)) {
            return DB::raw(
                "ST_GeomFromText('POINT({$value['lng']} {$value['lat']})', 4326)"
            );
        }
        
        return DB::raw("ST_GeomFromText('$value', 4326)");
    }
}
