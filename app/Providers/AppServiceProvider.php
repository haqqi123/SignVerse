<?php

namespace App\Providers;

use App\Services\Contracts\GestureRecognitionService;
use App\Services\MockGestureRecognitionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // AI service nyata (YOLOv8 via Python) disambungkan di phase 9;
        // untuk sekarang pakai mock deterministik agar alur practice bisa jalan.
        $this->app->bind(GestureRecognitionService::class, MockGestureRecognitionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
