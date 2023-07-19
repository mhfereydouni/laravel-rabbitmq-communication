<?php

namespace MHFereydouni\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQ
{
    private AMQPStreamConnection $connection;

    private AMQPChannel $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            host: config('rabbitmq.host'),
            port: config('rabbitmq.port'),
            user: config('rabbitmq.user'),
            password: config('rabbitmq.password'),
            vhost: config('rabbitmq.vhost'),
            heartbeat: config('rabbitmq.heartbeat', 0),
        );

        $this->channel = $this->connection->channel();
    }

    public function queue(): RabbitMQQueue
    {
        return new RabbitMQQueue($this->channel);
    }

    public function exchange(): RabbitMQExchange
    {
        return new RabbitMQExchange($this->connection);
    }

    public function message(): RabbitMQMessage
    {
        return new RabbitMQMessage($this->channel);
    }

    public function consume(): RabbitMQConsumer
    {
        return new RabbitMQConsumer($this->channel);
    }

    public function __destruct()
    {
        $this->connection->close();
        $this->channel->close();
    }
}
