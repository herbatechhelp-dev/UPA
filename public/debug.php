<?php

declare(strict_types=1);

// Boot Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// We just need the container to run Artisan commands and get paths
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTIK LARAVEL UPA ===\n\n";
echo "Base Path: " . base_path() . "\n";
echo "Public Path: " . public_path() . "\n";

$manifestPath = public_path('build/manifest.json');
echo "Manifest Path: " . $manifestPath . "\n";
echo "Manifest Exists: " . (file_exists($manifestPath) ? 'YES' : 'NO') . "\n";

if (file_exists($manifestPath)) {
    echo "\n=== ISI MANIFEST ===\n";
    echo file_get_contents($manifestPath) . "\n";
}

echo "\n=== CLEARING VIEW CACHE VIA ARTISAN ===\n";
try {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "Artisan view:clear: " . trim(\Illuminate\Support\Facades\Artisan::output()) . "\n";
} catch (\Exception $e) {
    echo "Error view:clear: " . $e->getMessage() . "\n";
}

echo "\n=== MANUAL VIEW CACHE CLEARING ===\n";
$viewPath = storage_path('framework/views');
if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*.php');
    echo "Menemukan " . count($files) . " file view terkompilasi.\n";
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            echo "Menghapus cache view: " . basename($file) . "\n";
        }
    }
} else {
    echo "Folder view cache tidak ditemukan!\n";
}

echo "\n=== SELESAI ===\n";
