<?php

namespace Allumina\PlaydCore\Tests;

use Orchestra\Testbench\TestCase;
use Allumina\PlaydCore\PlaydCoreServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [PlaydCoreServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
