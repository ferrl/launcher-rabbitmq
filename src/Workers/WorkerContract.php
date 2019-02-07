<?php declare(strict_types=1);

namespace RabbitMQ\Workers;

interface WorkerContract
{
    /**
     * Handle the payload.
     *
     * @param array $payload
     */
    public function run($payload = []): void;
}
