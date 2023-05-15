<?php

namespace MHFereydouni\RabbitMQ\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EventJobWrapper implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $event,
        public array $payload
    ) {}

    public function handle(): void
    {
        event(resolve($this->event, $this->payload));
    }

    public function viaQueue(): string
    {
        return config('rabbitmq.queue-name', 'default');
    }
}
