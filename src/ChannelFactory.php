<?php

namespace Brickhouse\Rebound;

use Amp\Socket\Socket;
use Brickhouse\Rebound\Server\HttpClientDriver;
use Brickhouse\Http\Request;

class ChannelFactory
{
    /**
     * Gets all the transport factory instances available.
     *
     * @var array<int,TransportFactory>
     */
    private readonly array $factories;

    public function __construct()
    {
        $this->factories = [
            resolve(\Brickhouse\Rebound\Websocket\TransportFactory::class),
        ];
    }

    /**
     * Upgrades the given request to a channel transport, if one supports the request.
     *
     * @param Request $request
     *
     * @return boolean  `true` if the request was upgraded. Otherwise, `false`.
     */
    public function upgrade(Socket $socket, Request $request, HttpClientDriver $driver): bool
    {
        foreach ($this->factories as $factory) {
            if (!$factory->shouldUpgrade($request)) {
                continue;
            }

            $transport = $factory->create($request);
            $transport->upgrade($socket, $request, $driver);

            return true;
        }

        return false;
    }
}
