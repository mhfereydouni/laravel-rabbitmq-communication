<?php

namespace MHFereydouni\RabbitMQ\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use MHFereydouni\RabbitMQ\RabbitMQServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            RabbitMQServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
    }
}
