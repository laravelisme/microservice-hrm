<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\HariLiburCreated::class => [
            \App\Listeners\AttachCompaniesToHariLibur::class,
        ],
        \App\Events\HariLiburUpdated::class => [
            \App\Listeners\SyncCompaniesToHariLibur::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
