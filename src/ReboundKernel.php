<?php

namespace Brickhouse\Rebound;

use Brickhouse\Core\Kernel;
use Brickhouse\Rebound\Server\SocketServer;
use Brickhouse\Log\Log;

final readonly class ReboundKernel implements Kernel
{
    public function invoke(array $args = [])
    {
        [$hostname, $port] = $this->parseHostingParameters($args);

        try {
            $server = resolve(SocketServer::class);
            $server->expose("{$hostname}:{$port}");
            $server->serve();
        } catch (\Throwable $e) {
            Log::error("Socket server closed unexpectedly: {message}", [
                'message' => $e->getMessage()
            ]);

            return 1;
        }

        // Await a termination signal to be received.
        if (!isset($args['NO_TRAP'])) {
            \Amp\trapSignal([\SIGHUP, \SIGINT, \SIGQUIT, \SIGTERM]);
        }

        return 0;
    }

    /**
     * Parse the kernel input parameters into a valid hostname and port.
     *
     * @param array<string,mixed> $args
     *
     * @return array{0:string,1:int}
     */
    public function parseHostingParameters(array $args): array
    {
        $hostname = $args['hostname'] ?? '127.0.0.1';
        $port = $args['port'] ?? 9000;

        if ($hostname === 'localhost') {
            $hostname = '127.0.0.1';
        }

        if ($port <= 0 || $port > 65535) {
            throw new \InvalidArgumentException("Invalid port number given (must be between 1-65535): {$port}.");
        }

        return [$hostname, $port];
    }
}
