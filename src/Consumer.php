<?php declare(strict_types=1);

namespace RabbitMQ;

use Closure;

class Consumer extends Connector
{
    /**
     * Publish a new message to the exchange.
     *
     * @param string $event
     * @param string $worker
     * @throws \ErrorException
     */
    public function consume($event, $worker)
    {
        $this->open();

        $exchange = config('rabbit-mq.exchange.name');
        $consumerTag = '';
        $queueName = $this->createQueue();

        $this->channel->queue_bind($queueName, $exchange, $event);

        $callback = $this->createCallback($worker);

        $this->channel->basic_consume($queueName, $consumerTag, false, false, false, false, $callback);

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        if (! $this->persistent) {
            $this->close();
        }
    }

    /**
     * Create a new queue.
     *
     * @return string
     */
    private function createQueue(): string
    {
        return data_get($this->channel->queue_declare(), 0);
    }

    /**
     * Create callback function for worker.
     *
     * @param string $workerType
     * @return Closure
     */
    private function createCallback($workerType): Closure
    {
        return function ($message) use ($workerType) {
            $worker = new $workerType;
            $payload = json_decode($message->body, true);

            call_user_func([$worker, 'consume'], $message, $payload);
        };
    }
}
