<?php

namespace Brickhouse\Rebound;

use Brickhouse\Http\Route;
use Brickhouse\Http\Router;

final readonly class Rebound
{
    /**
     * Mounts Rebound on the given URI in the application.
     *
     * @param string            $uri
     * @param callable|string   $callback
     * @param null|string       $action
     *
     * @return Route
     */
    public static function mount(
        string $uri,
        callable|string $callback,
        null|string $action = null
    ): Route {
        return Router::get($uri, $callback, $action);
    }
}
