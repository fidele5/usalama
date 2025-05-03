<?php

namespace App\Services;

use App\Events\AlertCreated;
use App\Jobs\SendSmsAlert;
use App\Models\Alert;
use App\Models\Responder;
use App\Models\User;
use App\Notifications\NewAlertNotification;
use App\Notifications\ResponderAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AlertService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    /**
     * Handle the Alert "created" event.
     */
    public function notifyNearbyUsers(Alert $alert)
    {
        $recipients = User::
            // ->where('id', '!=', $alert->user_id)
            // ->whereRaw(
            //     "ST_Distance_Sphere(
            //         coordinates,
            //         ST_GeomFromText(?, 4326)
            //     ) <= 10000", // 10km in meters
            //     [
            //         DB::selectOne(
            //             "SELECT ST_AsText(location) as wkt FROM alerts WHERE id = ?",
            //             [$alert->id]
            //         )->wkt
            //     ]
            // )
            pluck('phone')
            ->toArray();

            //$message = $this->compileSmsTemplate($alert);
            $message = "Mon super alert";

        //if (!empty($recipients)) {
            SendSmsAlert::dispatch(
                $recipients,
                $message,
                'emergency_alert'
            )->onQueue('sms');
        //}
    }

    public function dispatchNotifications(Alert $alert): void
    {
        $this->notifyNearbyUsers($alert);
        $this->broadcastNearbyUsers($alert);
        // Optionally, you can also send a notification to the creator
        // $this->notifyCreator($alert);
        // Optionally, you can also send a notification to the responders
        // $this->notifyResponders($alert); 
    }

    protected function notifyCreator(Alert $alert): void
    {
        $alert->user->notify(new NewAlertNotification($alert));
    }

    protected function notifyResponders(Alert $alert): void
    {
        $responders = Responder::with('users')
            ->nearAlert($alert)
            ->get()
            ->pluck('users')
            ->flatten();

        Notification::send($responders, new ResponderAlertNotification($alert));
    }

    protected function broadcastNearbyUsers(Alert $alert): void
    {
        // Broadcast the alert to nearby users
        broadcast(new AlertCreated($alert))->toOthers();
    }

    private function compileSmsTemplate(Alert $alert): string
    {
        return "EMERGENCY: {$alert->alertType->name}\n"
            . "Location: {$alert->address}\n"
            . "Time: {$alert->created_at->format('H:i')}\n"
            . "Details: ".route('alerts.show', $alert->id);
    }
}
