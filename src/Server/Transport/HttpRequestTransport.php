<?php

namespace Brickhouse\Rebound\Server\Transport;

use Amp\ByteStream\ClosedException;
use Amp\Socket\Socket;
use Brickhouse\Rebound\Exceptions\HttpClientException;
use Brickhouse\Rebound\Server\HttpClientDriver;
use Brickhouse\Http\Transport\Status;
use Brickhouse\Http\Request;

class HttpRequestTransport extends HttpMessageTransport
{
    /**
     * Deccodes the HTTP request from the given socket and returns it.
     *
     * @param Socket    $socket
     *
     * @return Request
     */
    public function receive(Socket $socket): Request
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

        if (!preg_match("/^(?<start>((?<method>[^ ]+) (?<path>(\S+)) HTTP\/(?<version>(\d+(?:\.\d+)?))\r\n))/", $rawHeaders, $matches)) {
            throw new HttpClientException(
                $socket,
                "Invalid start line in request",
                Status::BAD_REQUEST
            );
        }

        [
            'method' => $method,
            'path' => $path,
            'version' => $version,
        ] = $matches;

        $rawHeaders = substr($rawHeaders, strlen($matches['start']));
        $headers = $this->parseRawHeaders($rawHeaders);

        $uri = $this->parseHostHeader($socket, $headers->get('host'), $path);

        [$body, $contentLength] = $this->parseMessageBody($socket, $buffer, $headers);

        return new Request(
            $method,
            $uri,
            $headers,
            $body,
            $contentLength,
            $version
        );
    }

    /**
     * Encodes the HTTP request and sends over the given socket.
     *
     * @param Request   $request
     * @param Socket    $socket
     *
     * @return void
     */
    public function send(Request $request, Socket $socket): void
    {
        $method = $request->method();
        $uri = $request->uri()->__toString();
        $protocol = $request->getProtocolVersion();

        $statusLine = "{$method} {$uri} HTTP/{$protocol}\r\n";
        $headers = $request->headers->serialize();
        $body = $request->content();

        $socket->write($statusLine);
        $socket->write($headers);

        $socket->write("\r\n\r\n");

        while (!$body->eof()) {
            $socket->write($body->read(1024));
        }
    }
}
