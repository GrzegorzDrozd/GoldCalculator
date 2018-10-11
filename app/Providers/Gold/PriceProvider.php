<?php

namespace App\Providers\Gold;

use App\Contracts\DataSource;
use App\Services\Gold\NBPGoldSource;
use Illuminate\Support\ServiceProvider;

class PriceProvider extends ServiceProvider
{
    /**
     * Register data sources
     */
    public function register(){
        $this->app->bind(DataSource::class, NBPGoldSource::class);
    }
}
