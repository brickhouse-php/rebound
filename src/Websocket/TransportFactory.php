<?php

namespace Brickhouse\Rebound\Websocket;

use Brickhouse\Http\Request;

class TransportFactory implements \Brickhouse\Rebound\TransportFactory
{
    /**
     * @inheritDoc
     */
    public function shouldUpgrade(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        $upgradeHeader = $request->headers->get('upgrade');
        if (!$upgradeHeader || strcasecmp($upgradeHeader, 'websocket')) {
            return false;
        }

        $connectionHeader = $request->headers->get('connection');
        if (!$connectionHeader || strcasecmp($connectionHeader, 'upgrade')) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function create(Request $request): Transport
    {
        return new Transport;
    }
}
