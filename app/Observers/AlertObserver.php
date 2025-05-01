<?php

namespace App\Observers;

use App\Models\Alert;
use App\Services\AlertService;

class AlertObserver
{
    public function __construct(
        private AlertService $alertService
    ) {}

    /**
     * Handle the Alert "created" event.
     */
    public function created(Alert $alert): void
    {
        $this->alertService->dispatchNotifications($alert);
    }

    /**
     * Handle the Alert "updated" event.
     */
    public function updated(Alert $alert): void
    {
        //
    }

    /**
     * Handle the Alert "deleted" event.
     */
    public function deleted(Alert $alert): void
    {
        //
    }

    /**
     * Handle the Alert "restored" event.
     */
    public function restored(Alert $alert): void
    {
        //
    }

    /**
     * Handle the Alert "force deleted" event.
     */
    public function forceDeleted(Alert $alert): void
    {
        //
    }
}
