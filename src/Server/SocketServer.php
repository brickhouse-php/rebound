<?php

namespace Brickhouse\Rebound\Server;

use Amp\Socket\ResourceServerSocketFactory;
use Amp\Socket\SocketAddress;
use Amp\Socket\ServerSocket;
use Amp\Socket\ServerSocketFactory;
use Amp\Socket\Socket;
use Brickhouse\Log\Log;
use Revolt\EventLoop;

final class SocketServer
{
    /** @var array<string,SocketAddress> */
    private array $addresses = [];

    /** @var list<ServerSocket> */
    private array $servers = [];

    /** @var array<int,HttpClientDriver> */
    private array $drivers = [];

    /** @var SocketClientFactory */
    private readonly SocketClientFactory $clientFactory;

    /** @var ServerSocketFactory */
    private readonly ServerSocketFactory $socketFactory;

    /**
     * Undocumented function
     */
    public function __construct()
    {
        $this->clientFactory = new SocketClientFactory();
        $this->socketFactory = new ResourceServerSocketFactory();
    }

    /**
     * Undocumented function
     *
     * @param string    $endpoint
     *
     * @return void
     */
    public function expose(string $endpoint): void
    {
        $address = SocketAddress\fromString($endpoint);
        $name = $address->toString();

        if (isset($this->addresses[$name])) {
            throw new \Exception("Already listening to {$name} on socket server");
        }

        $this->addresses[$name] = $address;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function serve(): void
    {
        if (empty($this->addresses)) {
            throw new \Exception("No addresses specified on server");
        }

        try {
            foreach ($this->addresses as $address) {
                $this->servers[] = $this->socketFactory->listen($address);
            }

            foreach ($this->servers as $server) {
                // Using short-closure to avoid Psalm bug when using a first-class callable here.
                EventLoop::queue(function () use ($server) {
                    while ($socket = $server->accept()) {
                        EventLoop::queue($this->handleClient(...), $socket);
                    }
                });
            }
        } catch (\Throwable $e) {
            $this->terminate();

            throw $e;
        }
    }

    public function terminate(): void
    {
        foreach ($this->servers as $server) {
            $server->close();
        }

        $this->servers = [];
    }

    protected function handleClient(Socket $socket): void
    {
        try {
            $client = $this->clientFactory->createClient($socket);
            if (!$client) {
                $socket->close();
                return;
            }

            $id = $client->id();
            $this->drivers[$id] = resolve(HttpClientDriver::class, [$client]);

            try {
                $this->drivers[$id]->handleClient($socket);
            } finally {
                unset($this->drivers[$id]);
            }
        } catch (\Throwable $e) {
            Log::channel('rebound')->error(
                "Unhandled exception while handling client {address}",
                ['exception' => $e, 'address' => $socket->getRemoteAddress()->toString()]
            );

            $socket->close();
        }
    }
}
