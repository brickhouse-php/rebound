<?php

namespace Brickhouse\Rebound\Server;

use Amp\ByteStream\StreamException;
use Amp\Future;
use Amp\Socket\Socket;
use Brickhouse\Rebound\ChannelFactory;
use Brickhouse\Rebound\Server\Transport\HttpRequestTransport;
use Brickhouse\Rebound\Server\Transport\HttpResponseTransport;
use Brickhouse\Http\Request;
use Brickhouse\Http\Response;
use Brickhouse\Http\Transport\Status;
use Brickhouse\Log\Log;
use function Amp\async;

class HttpClientDriver
{
    public const int HEADER_SIZE_LIMIT = 4096;

    public const DEFAULT_BODY_SIZE_LIMIT = 131072;

    /**
     * @var Future<null>
     */
    private Future $pendingResponse;

    public function __construct(
        private readonly SocketClient $client,
        private readonly ChannelFactory $channelFactory,
    ) {
        $this->pendingResponse = Future::complete();
    }

    public function handleClient(Socket $socket): void
    {
        $transport = resolve(HttpRequestTransport::class);

        try {
            $request = $transport->receive($socket);

            $this->pendingResponse = async(
                $this->handleRequest(...),
                $socket,
                $request
            );

            $this->pendingResponse->await();
        } catch (\Throwable $e) {
            Log::error("Failed to handle request: {message}", [
                'message' => $e->getMessage(),
                'exception' => $e
            ]);
        } finally {
            $this->pendingResponse->ignore();
        }
    }

    protected function handleRequest(Socket $socket, Request $request): void
    {
        if ($this->channelFactory->upgrade($socket, $request, $this)) {
            return;
        }

        $response = Response::json([
            'status' => 'No applicable transport found.'
        ])->withStatus(Status::BAD_REQUEST);

        $this->send($socket, $response);
    }

    public function send(Socket $socket, Response $response, bool $close = true): void
    {
        $transport = resolve(HttpResponseTransport::class);

        try {
            $transport->send($response, $socket);
        } catch (StreamException) {
            return;
        } finally {
            if ($close) {
                $this->client->close();
            }
        }
    }
}
