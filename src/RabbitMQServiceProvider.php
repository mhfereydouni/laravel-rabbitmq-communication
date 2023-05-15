<?php

namespace MHFereydouni\RabbitMQ;

use Illuminate\Contracts\Queue\Factory as QueueFactoryContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use MHFereydouni\RabbitMQ\Commands\ConsumeEventMessages;
use MHFereydouni\RabbitMQ\Commands\DeclareEventExchanges;

class RabbitMQServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(RabbitMQ::class, function () {
            return new RabbitMQ();
        });

        $this->app->extend('events', function (Dispatcher $dispatcher, $app) {
            return (new RabbitMQDispatcher($app))->setQueueResolver(function () use ($app) {
                return $app->make(QueueFactoryContract::class);
            });
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/rabbitmq.php',
            'rabbitmq'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DeclareEventExchanges::class,
                ConsumeEventMessages::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/rabbitmq.php' => config_path('rabbitmq.php'),
        ], 'laravel-rabbitmq-communication-config');
    }
}
