<?php

namespace Brickhouse\Rebound\Server\Transport;

use Amp\ByteStream\ClosedException;
use Amp\Socket\Socket;
use Brickhouse\Rebound\Exceptions\HttpClientException;
use Brickhouse\Rebound\Server\HttpClientDriver;
use Brickhouse\Http\Transport\Status;
use Brickhouse\Http\Response;

class HttpResponseTransport extends HttpMessageTransport
{
    /**
     * Deccodes the HTTP response from the given socket and returns it.
     *
     * @param Socket    $socket
     *
     * @return Response
     */
    public function receive(Socket $socket): Response
    {
        $buffer = $socket->read();
        if (!$buffer) {
            throw new HttpClientException($socket, "Cannot read request: socket stream is empty.");
        }

        $buffer = \ltrim($buffer, "\r\n");
        $rawHeaders = "";

        do {
            if ($headerEnd = strpos($buffer, "\r\n\r\n")) {
                $rawHeaders = substr($buffer, 0, $headerEnd + 2);
                $buffer = substr($buffer, $headerEnd + 4);
                break;
            }

            $length = strlen($buffer);
            $maxLength = HttpClientDriver::HEADER_SIZE_LIMIT;

            if ($length > $maxLength) {
                throw new HttpClientException(
                    $socket,
                    "Header size exceeded ({$length} > {$maxLength})",
                    Status::REQUEST_HEADER_FIELDS_TOO_LARGE
                );
            }

            $chunk = $socket->read();
            if ($chunk === null) {
                throw new ClosedException("Socket stream is closed.");
            }

            $buffer .= $chunk;
        } while (true);

        if (!preg_match("/^(?<start>(HTTP\/(?<version>(\d+(?:\.\d+)?)) (?<code>\d{3}) (?<status>(.+))))/", $rawHeaders, $matches)) {
            throw new HttpClientException(
                $socket,
                "Invalid start line in response",
                Status::BAD_REQUEST
            );
        }

        [
            'version' => $version,
            'code' => $code,
        ] = $matches;

        $rawHeaders = substr($rawHeaders, strlen($matches['start']));
        $headers = $this->parseRawHeaders($rawHeaders);

        [$body] = $this->parseMessageBody($socket, $buffer, $headers);

        $response = new Response(
            intval($code),
            $headers,
            protocol: $version
        );

        $response->setBody($body);

        return $response;
    }

    /**
     * Encodes the HTTP response and sends over the given socket.
     *
     * @param Response  $response
     * @param Socket    $socket
     *
     * @return void
     */
    public function send(Response $response, Socket $socket): void
    {
        $protocol = $response->getProtocolVersion();

        $status = $response->status;
        $reason = Status::getReason($status);

        $statusLine = "HTTP/{$protocol} {$status} {$reason}\r\n";
        $headers = $response->headers->serialize();
        $body = $response->getBody();

        $socket->write($statusLine);
        $socket->write($headers);

        $socket->write("\r\n\r\n");

        while (!$body->eof()) {
            $socket->write($body->read(1024));
        }

        if (($handler = $response->getUpgradeHandler()) !== null) {
            $handler();
        }
    }
}
