<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('path.public', function() {
            return is_dir(base_path('public_html')) ? base_path('public_html') : base_path('public');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
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
            if (function_exists('symlink')) {
                $publicDir = is_dir(base_path('public_html')) ? base_path('public_html') : public_path();
                $symlinkPath = $publicDir . '/storage';
                $targetPath = storage_path('app/public');
                if (!file_exists($symlinkPath) && is_dir($targetPath)) {
                    @symlink($targetPath, $symlinkPath);
                }
            }
        } catch (\Throwable $e) {
            // Silently fail - do not block application boot
            \Illuminate\Support\Facades\Log::warning('AppServiceProvider boot error: ' . $e->getMessage());
        }
    }
}
