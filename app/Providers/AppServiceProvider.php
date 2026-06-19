<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure storage directories exist (critical for hosting environments)
        $publicStoragePath = storage_path('app/public');
        if (!is_dir($publicStoragePath)) {
            @mkdir($publicStoragePath, 0755, true);
        }
        $materialsPath = $publicStoragePath . '/materials';
        if (!is_dir($materialsPath)) {
            @mkdir($materialsPath, 0755, true);
        }

        // Auto-create storage symlink for hosting (public_html or public)
        $publicDir = file_exists(base_path('public_html')) ? base_path('public_html') : public_path();
        $symlinkPath = $publicDir . '/storage';
        $targetPath = storage_path('app/public');
        if (!file_exists($symlinkPath) && is_dir($targetPath)) {
            @symlink($targetPath, $symlinkPath);
        }
    }
}
