<?php

namespace App\Events;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlertCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Alert $alert){}
   

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Get users within 10km and create private channels for each
        return User::select('id')
            ->where('id', '!=', $this->alert->user_id) // Exclude creator
            ->whereRaw(
                "ST_Distance_Sphere(
                    location, 
                    ST_GeomFromText(?, 4326)
                ) <= 10000", // 10km in meters
                [$this->alert->location->toWkt()]
            )
            ->get()
            ->map(fn ($user) => new PrivateChannel("user.{$user->id}"))->toArray();
    }

    public function broadcastWith()
    {
        return [
            'alert_id' => $this->alert->public_id,
            'type' => $this->alert->alertType->name,
            'distance' => $this->calculateDistance(),
            'location' => $this->getCoordinates(),
            'description' => $this->alert->description,
            'address' => $this->alert->address,
            'time' => $this->alert->created_at->diffForHumans()
        ];
    }

    protected function calculateDistance(): float
    {
        return User::selectRaw(
            "ST_Distance_Sphere(
                coordinates, 
                ST_GeomFromText(?, 4326)
            ) as distance",
            [$this->alert->location->toWkt()]
        )->first()->distance / 1000; // Convert to km
    }

    protected function getCoordinates(): array
    {
        $point = $this->alert->location;
        return [
            'lat' => DB::selectOne("SELECT ST_Y(?) as lat", [$point])->lat,
            'lng' => DB::selectOne("SELECT ST_X(?) as lng", [$point])->lng
        ];
    }
}
