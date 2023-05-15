<?php

namespace MHFereydouni\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQConsumer
{
    private AMQPChannel $channel;

    private int $qos = 1;

    private bool $acknowledge = false;

    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    public function receiveWithoutAcknowledgement(int $numberOfMessages): RabbitMQConsumer
    {
        $this->qos = $numberOfMessages;

        return $this;
    }

    public function from(string $queue, callable $handle): RabbitMQConsumer
    {
        $this->channel->basic_qos(null, $this->qos, null);

        $this->channel->basic_consume(
            $queue,
            '',
            false,
            ! $this->acknowledge,
            false,
            false,
            function (AMQPMessage $message) use ($handle) {
                call_user_func_array($handle, [
                    json_decode($message->getBody(), true),
                    $message->getRoutingKey(),
                ]);

                if ($this->acknowledge) {
                    $message->ack();
                }
            }
        );

        return $this;
    }

    public function acknowledge(): RabbitMQConsumer
    {
        $this->acknowledge = true;

        return $this;
    }

    public function receive(): void
    {
        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    public function __destruct()
    {
        $this->channel->close();
    }
}
