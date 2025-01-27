<?php

namespace Brickhouse\Rebound\Exceptions;

use Amp\Socket\Socket;
use Brickhouse\Rebound\Server\SocketClient;
use Brickhouse\Http\Transport\Status;

class HttpClientException extends \Exception
{
    public function __construct(
        public readonly Socket|SocketClient $client,
        string $message,
        int $code = Status::BAD_REQUEST,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
