<?php declare(strict_types=1);

namespace RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Connector
{
    /**
     * Should the connection be persisted?
     *
     * @var bool
     */
    protected $persistent;

    /**
     * Publisher constructor.
     *
     * @param bool $persistent
     */
    public function __construct($persistent = false)
    {
        $this->persistent = $persistent;
    }

    /**
     * @var AbstractConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * Create a new connection.
     *
     * @return AbstractConnection
     */
    protected function createConnection(): AbstractConnection
    {
        $connection = config('rabbit-mq.default');

        $type = config("rabbit-mq.connections.{$connection}.type");
        $host = config("rabbit-mq.connections.{$connection}.host");
        $port = config("rabbit-mq.connections.{$connection}.port");
        $user = config("rabbit-mq.connections.{$connection}.user");
        $password = config("rabbit-mq.connections.{$connection}.password");

        return new $type($host, $port, $user, $password);
    }

    /**
     * Create a new channel.
     *
     * @return AMQPChannel
     */
    protected function createChannel(): AMQPChannel
    {
        $channel = $this->connection->channel();

        $name = config('rabbit-mq.exchange.name');
        $type = config('rabbit-mq.exchange.type');
        $passive = config('rabbit-mq.exchange.passive');
        $durable = config('rabbit-mq.exchange.durable');
        $autoDelete = config('rabbit-mq.exchange.auto_delete');

        $channel->exchange_declare($name, $type, $passive, $durable, $autoDelete);

        return $channel;
    }

    /**
     * Create a new AMQPMessage.
     *
     * @param array $payload
     * @return AMQPMessage
     */
    protected function createMessage($payload = []): AMQPMessage
    {
        return new AMQPMessage(json_encode($payload));
    }

    /**
     * Open connection and channel.
     */
    public function open(): void
    {
        if (is_null($this->connection) && is_null($this->channel)) {
            $this->connection = $this->createConnection();
            $this->channel = $this->createChannel();
        }
    }

    /**
     * Close all channels and connections.
     */
    public function close()
    {
        if (! (is_null($this->connection) || is_null($this->connection))) {
            $this->channel->close();
            $this->connection->close();
        }

        $this->channel = null;
        $this->connection = null;
    }
}
