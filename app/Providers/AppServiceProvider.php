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
        // Recognizer mock dipakai di phase 4—8; diganti AIRecognitionService
        // (YOLOv8 via Python service) di phase 9 tanpa ubah konsumen lain.
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
