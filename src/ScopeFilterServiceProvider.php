<?php

namespace Jsadways\ScopeFilter;

use Illuminate\Support\ServiceProvider;

class ScopeFilterServiceProvider extends ServiceProvider
{
    use ScopeFilterTrait;
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //

    }
}
