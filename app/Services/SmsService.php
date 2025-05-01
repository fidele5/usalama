<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $username;
    protected $password;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->username = env('BULKSMS_USERNAME'); // Set your BulkSMS username in .env
        $this->password = env('BULKSMS_PASSWORD'); // Set your BulkSMS password in .env
    }

    /**
     * Send an SMS using BulkSMS.
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function send($phone, $message)
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->post(env('BULKSMS_API_URL'), [
                'to' => $phone,
                'body' => $message,
            ]);

        return $response->successful();
    }
}
