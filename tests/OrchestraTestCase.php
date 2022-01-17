<?php

namespace Aldemeery\Shieldify\Tests;

use Mockery;
use Orchestra\Testbench\TestCase;
use Aldemeery\Shieldify\ShieldifyServiceProvider;

abstract class OrchestraTestCase extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    protected function getPackageProviders($app)
    {
        return [
            ShieldifyServiceProvider::class,
        ];
    }
}
