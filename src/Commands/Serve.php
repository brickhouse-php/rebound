<?php

namespace Brickhouse\Rebound\Commands;

use Brickhouse\Console\Attributes\Option;
use Brickhouse\Console\Command;
use Brickhouse\Console\InputOption;
use Brickhouse\Core\Application;
use Brickhouse\Rebound\ReboundKernel;

class Serve extends Command
{
    /**
     * The name of the console command.
     *
     * @var string
     */
    public string $name = 'rebound:serve';

    /**
     * The description of the console command.
     *
     * @var string
     */
    public string $description = 'Starts the Rebound server.';

    /**
     * The hostname to serve on.
     *
     * @var null|string
     */
    #[Option("host", input: InputOption::REQUIRED, description: 'Specify the hostname to serve on.')]
    public null|string $hostname = null;

    /**
     * The post to serve on.
     *
     * @var null|int
     */
    #[Option("port", input: InputOption::REQUIRED, description: 'Specify the port to serve on.')]
    public null|int $port = null;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hostname = $this->hostname ?? env('REBOUND_SERVER_HOST', '127.0.0.1');
        $port = $this->port ?? env('REBOUND_SERVER_PORT', 9000);

        $this->writeHtml(<<<HTML
            <div class="w-52 my-1">
                <div class="w-full justify-center mb-1">
                    <span class="bg-teal-600 text-white font-bold px-2">Rebound</span>
                </div>
                <div class="w-full justify-center ml-2">
                    Real-time server for Brickhouse.
                </div>
            </div>
        HTML);

        $this->info("Server running on <span class='font-bold'>[http://{$hostname}:{$port}]</span>.");
        $this->comment("Press Ctrl+C to stop the server.");

        return Application::current()->kernel(
            ReboundKernel::class,
            [
                'hostname' => $hostname,
                'port' => $port,
            ]
        );
    }
}
