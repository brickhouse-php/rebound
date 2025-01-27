<?php

namespace Brickhouse\Rebound\Websocket;

class Frame
{
    /**
     * @param boolean               $finished   Defines whether this is the last frame of a fragmented payload.
     * @param WebsocketFrameType    $opcode     Defines the opcode of the frame.
     * @param string                $payload    Defines the content of the frame, if any.
     */
    public function __construct(
        public readonly bool $finished,
        public readonly WebsocketFrameType $opcode,
        private string $payload = '',
    ) {
        $this->payload = $payload;
    }

    /**
     * Gets whether the frame is a control frame.
     *
     * @return boolean
     */
    public function isControlFrame(): bool
    {
        return (bool) ($this->opcode->value & 0x8);
    }

    /**
     * Gets the payload of the frame.
     *
     * @return string
     */
    public function payload(): string
    {
        return $this->payload;
    }

    /**
     * Sets the payload of the frame.
     *
     * @param   string  $payload
     *
     * @return void
     */
    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }
}
