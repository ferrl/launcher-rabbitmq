<?php declare(strict_types=1);

namespace RabbitMQ\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMQ\Consumer;
use RabbitMQ\Workers\WorkerContract;

class BindWorkers extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'bind-workers';

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bind workers to RabbitMQ events.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \ErrorException
     */
    public function handle()
    {
        $consumer = new Consumer;
        $consumer->consume('*', static::class);
    }

    /**
     * Consume a message.
     *
     * @param AMQPMessage $message
     * @param array $payload
     * @return void
     */
    public function consume(AMQPMessage $message, $payload)
    {
        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];
        $routingKey = $message->delivery_info['routing_key'];
        $deliveryTag = $message->delivery_info['delivery_tag'];

        $workers = data_get(app('rabbit-mq-bindings'), $routingKey, []);

        $acknowledged = true;

        foreach ($workers as $workerType) {
            /** @var WorkerContract $worker */
            $worker = new $workerType;

            try {
                $worker->run($payload);
            } catch (Exception $exception) {
                $acknowledged = false;
            }
        }

        if ($acknowledged) {
            $channel->basic_ack($deliveryTag);
        }
    }
}
