<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Mengarahkan Laravel untuk membaca file tampilan dari folder frontend/views
        View::addLocation(base_path('../frontend/views'));
    }
}