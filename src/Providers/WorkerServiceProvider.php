<?php declare(strict_types=1);

namespace RabbitMQ\Providers;

use Illuminate\Support\ServiceProvider;

class WorkerServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'example_event' => [
            'ExampleWorkerClass',
        ],
    ];

    /**
     * Register any RabbitMQ events.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('rabbit-mq-bindings', function () {
            return $this->listen;
        });
    }
}
