<?php

namespace Brickhouse\Rebound\Server;

use Amp\Socket\Socket;
use Amp\Socket\SocketAddress;

class SocketClient
{
    private static int $nextId = 0;
    private readonly int $id;

    public function __construct(
        public readonly Socket $socket,
    ) {
        $this->id = self::$nextId++;
    }

    /**
     * Gets the unique Id of the socket client.
     *
     * @return integer
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * Gets the remote `SocketAddress` of the socket.
     *
     * @return SocketAddress
     */
    public function remoteAddress(): SocketAddress
    {
        return $this->socket->getRemoteAddress();
    }

    /**
     * Gets the local `SocketAddress` of the socket.
     *
     * @return SocketAddress
     */
    public function localAddress(): SocketAddress
    {
        return $this->socket->getLocalAddress();
    }

    /**
     * Closes the socket connection.
     *
     * @return void
     */
    public function close(): void
    {
        $this->socket->close();
    }

    /**
     * Registers a callback to invoke once the socket is closed.
     *
     * @param \Closure(): void      $onClose
     *
     * @return void
     */
    public function onClose(\Closure $onClose): void
    {
        $this->socket->onClose($onClose);
    }

    /**
     * Gets whether the socket is closed.
     *
     * @return boolean
     */
    public function closed(): bool
    {
        return $this->socket->isClosed();
    }
}
