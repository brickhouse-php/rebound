<?php

namespace Brickhouse\Rebound;

use Brickhouse\Http\Request;

interface TransportFactory
{
    /**
     * Determines whether the given update should be upgraded and handled by the transport implementation.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function shouldUpgrade(Request $request): bool;

    /**
     * Creates a new transport from the factory.
     *
     * @param Request $request
     *
     * @return Transport
     */
    public function create(Request $request): Transport;
}
