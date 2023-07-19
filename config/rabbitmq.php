<?php

return [
    /** -----------------------------------------------
     * connection settings
     */
    'host' => env('RABBITMQ_HOST', '127.0.0.1'),
    'port' => env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost' => env('RABBITMQ_VHOST', '/'),
    'heartbeat' => env('RABBITMQ_HEARTBEAT', 0),

    'event-consumers' => [
//        [
//            'event' => '\App\Events\MyEvent',
//            'routing_key' => 'my_routing_key', // if this event does not use routing key then remove this line
//            'map_into' => '\App\Events\MapIntoEvent', // if you want to use the same event then remove this line
//        ],
    ],

    /** -----------------------------------------------
     * options: 'sync', 'kind-sync', 'job'
     * sync: event are fired when they are consumed and error will stop the consumer
     * kind-sync: event are fired when they are consumed and error will not stop the consumer instead a log is stored
     * job: events are fired in a queue via laravel jobs (Note: you should make sure there is a queue worker for queue)
     */
    'event-consumer-mode' => 'sync',

    'log-channel' => env('RABBITMQ_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),

    'queue-name' => 'default',
];
