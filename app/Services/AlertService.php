<?php

namespace App\Services;

use App\Events\AlertCreated;
use App\Jobs\SendSmsAlert;
use App\Models\Alert;
use App\Models\Responder;
use App\Models\User;
use App\Notifications\NewAlertNotification;
use App\Notifications\ResponderAlertNotification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Geocoder\Geocoder;

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

            $message = $this->compileSmsTemplate($alert);

        if (!empty($recipients)) {
            SendSmsAlert::dispatch(
                $recipients,
                $message,
                'emergency_alert'
            )->onQueue('sms');
        }
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
        $client = new Client();
        $geocoder = new Geocoder($client);
        $address = $alert->address;
        $addressLink = "https://maps.google.com/?q=$address";
        $contactPhone = $alert->contact_phone ? $alert->contact_phone : $alert->user->phone;
        $geocoder->setApiKey(config('geocoder.key'));
        if (!is_null($alert->location) && !is_null($alert->location["lat"]) && !is_null($alert->location["lng"])) {
            $googleAddres =  $geocoder->getAddressForCoordinates($alert->location["lat"], $alert->location["lng"]);
            $address = $googleAddres['formatted_address'];
            $addressLink = "https://maps.google.com/?q=".$googleAddres["lat"] . ",".$googleAddres["lng"];
        }
        
        return 'Alert: ' . $alert->description . "\n" .
            "Adresse: $address \n" .
            "Contact: $contactPhone \n" .
            "Google Address: $addressLink \n";
    }
}
