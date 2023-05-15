<?php

namespace MHFereydouni\RabbitMQ\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MHFereydouni\RabbitMQ\RabbitMQ;
use MHFereydouni\RabbitMQ\Support\EventJobWrapper;

class ConsumeEventMessages extends Command
{
    protected $signature = 'rabbitmq:consume-events';

    protected $description = 'consume events in the rabbitmq';

    private array $events;

    public function __construct()
    {
        parent::__construct();

        $this->events = collect(config('rabbitmq.event-consumers'))
            ->map(function ($event) {
                return [
                    'base_event' => $event['event'],
                    'map_into_event' => $event['map_into'] ?? $event['event'],
                    'routing_key' => $event['routing_key'] ?? '',
                ];
            })
            ->toArray();
    }

    public function handle(RabbitMQ $rabbitmq): int
    {
        $queue = $rabbitmq
            ->queue()
            ->durable()
            ->name(config('app.name'))
            ->declare();

        foreach ($this->events as $event) {
            $queue->bindTo(class_basename($event['base_event']), $event['routing_key']);
        }

        $rabbitmq
            ->consume()
            ->acknowledge()
            ->from(config('app.name'), [$this, 'fireEvent'])
            ->receive();

        return Command::SUCCESS;
    }

    public function fireEvent(array $payload, string $routingKey)
    {
        $event = Arr::first($this->events, function (array $event) use ($routingKey, $payload) {
            return $payload['event.name'] === class_basename($event['base_event'])
                && Str::is($event['routing_key'], $routingKey);
        })['map_into_event'];

        $mode = config('rabbitmq.event-consumer-mode', 'sync');

        if ($mode === 'sync') {
            event(resolve($event, $payload));
        } elseif ($mode === 'kind-sync') {
            try {
                event(resolve($event, $payload));
            } catch (\Exception $exception) {
                Log::channel(config('rabbitmq.log-channel'))
                    ->error(
                        'Could not dispatch the event consumed from rabbitmq!',
                        [
                            'consumed_event' => $payload['event.name'],
                            'routing_key' => $routingKey,
                            'was_going_to_map_into' => $event,
                            'error_message' => $exception->getMessage(),
                            'error_trace' => $exception->getTraceAsString(),
                        ]
                    );
            }
        } elseif ($mode === 'job') {
            EventJobWrapper::dispatch($event, $payload);
        }
    }
}
