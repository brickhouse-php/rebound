<?php

namespace Brickhouse\Rebound\Websocket\Exceptions;

use Brickhouse\Rebound\Websocket\WebsocketCloseCode;

class WebsocketClientException extends \Exception
{
    public function __construct(
        string $message,
        int $code = WebsocketCloseCode::UNEXPECTED_CONDITION,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
