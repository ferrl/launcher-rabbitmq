<?php declare(strict_types=1);

namespace RabbitMQ;

class Publisher extends Connector
{
    /**
     * Publish a new message to the exchange.
     *
     * @param string $event
     * @param array $payload
     */
    public function publish($event, $payload = [])
    {
        $this->open();

        $exchange = config('rabbit-mq.exchange.name');
        $message = $this->createMessage($payload);

        $this->channel->basic_publish($message, $exchange, $event);

        if (! $this->persistent) {
            $this->close();
        }
    }
}
