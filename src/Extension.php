<?php

namespace Brickhouse\Rebound;

use Brickhouse\Core\Application;
use Brickhouse\Rebound\Commands;

class Extension extends \Brickhouse\Core\Extension
{
    /**
     * Gets the human-readable name of the extension.
     */
    public string $name = "brickhouse/rebound";

    public function __construct(
        private readonly Application $application
    ) {}

    /**
     * Invoked before the application has started.
     */
    public function register(): void
    {
        $this->application->singleton(ChannelFactory::class);

        $this->addCommands([
            Commands\Serve::class,
        ]);
    }

    /**
     * Invoked after the application has started.
     */
    public function boot(): void
    {
        //
    }
}
