<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $recipients,
        public string $message,
        public ?string $jobType = null
    ) {}

    public function handle(SmsService $smsService)
    {
        $results = $smsService->sendBulk($this->recipients, $this->message);
        Log::channel('sms')->info('Bulk SMS Job Completed', [
            'recipients' => count($this->recipients),
            'results' => $results,
            'job_type' => $this->jobType
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::channel('sms')->error('Bulk SMS Job Failed', [
            'recipients' => count($this->recipients),
            'error' => $exception->getMessage()
        ]);
    }
}
