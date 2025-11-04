<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
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
        View::composer(['layouts.app', 'layouts.guest'], function ($view) {
            $photos = collect();

            try {
                $disk = Storage::disk('public');
                if ($disk->exists('photos')) {
                    $photos = collect($disk->files('photos'))
                        ->filter(fn ($path) => preg_match('/\.(jpe?g|png|gif|webp)$/i', $path))
                        ->sort()
                        ->map(function ($path) {
                            $relative = Str::startsWith($path, 'public/')
                                ? Str::after($path, 'public/')
                                : $path;

                            return '/storage/' . ltrim($relative, '/');
                        })
                        ->values();
                }
            } catch (\Throwable $e) {
                $photos = collect();
            }

            $view->with('backgroundPhotos', $photos);
        });
    }
}
