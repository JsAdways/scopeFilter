<?php

namespace Jsadways\ScopeFilter\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
    }
}
