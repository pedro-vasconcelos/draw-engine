<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use PedroVasconcelos\DrawEngine\DrawServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Get application timezone.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return 'Europe/Lisbon';
    }
    
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }
    
    protected function getPackageProviders($app)
    {
        return [
            DrawServiceProvider::class,
        ];
    }
    
    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
