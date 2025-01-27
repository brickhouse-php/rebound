<?php

namespace Brickhouse\Rebound;

use Amp\Socket\Socket;
use Brickhouse\Rebound\Server\HttpClientDriver;
use Brickhouse\Http\Request;

interface Transport
{
    /**
     * Upgrades the given request using the transport implementation.
     *
     * @param Request $request
     *
     * @return void
     */
    public function upgrade(Socket $socket, Request $request, HttpClientDriver $driver): void;
}
