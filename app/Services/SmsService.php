<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $username;
    protected $password;
    protected $apiUrl;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->username = config('services.bulksms.username'); // Set your BulkSMS username in .env
        $this->password = config('services.bulksms.password'); // Set your BulkSMS password in .env
        $this->apiUrl = env('BULKSMS_API_URL', 'https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30'); // Set your BulkSMS API URL in .env
    }

    /**
     * Send single SMS
     */
    public function sendSingle(string $phone, string $message): bool
    {
        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(15)
                ->retry(3, 1000)
                ->post($this->apiUrl, [
                    'to' => $phone,
                    'body' => $message,
                    'routingGroup' => "PREMIUM",
                    'encoding' => "UNICODE"
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('BulkSMS API Error', [
                'status' => $response->status(),
                'response' => $response->body(),
                'phone' => substr($phone, -4) // Log last 4 digits for privacy
            ]);
            
            return false;
        } catch (\Exception $e) {
            Log::critical('BulkSMS Connection Failed', [
                'error' => $e->getMessage(),
                'phone' => substr($phone, -4)
            ]);
            return false;
        }
    }

    /**
     * Send bulk SMS (chunked automatically)
     */
    public function sendBulk(array $recipients, string $message, int $chunkSize = 100): array
    {
        $results = [];
        $chunks = array_chunk($recipients, $chunkSize);

        foreach ($chunks as $chunk) {
            $responses = Http::pool(fn (\Illuminate\Http\Client\Pool $pool) => 
                collect($chunk)->map(fn ($phone) => 
                    $pool->as($phone)
                        ->withBasicAuth($this->username, $this->password)
                        ->post($this->apiUrl, [
                            'to' => "243". (int) $phone,
                            'body' => $message,
                            'routingGroup' => "PREMIUM",
                            'encoding' => "TEXT"
                        ])
                )
            );

            foreach ($responses as $phone => $response) {
                $results[$phone] = $response->successful();
                
                if (!$response->successful()) {
                    Log::error('BulkSMS Failed Recipient', [
                        'phone' => substr($phone, -4),
                        'status' => $response->status()
                    ]);
                }
            }
            
            sleep(1); // Respect API rate limits
        }

        return $results;
    }
}
