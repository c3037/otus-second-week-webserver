<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection;

final class SocketConnection implements SocketConnectionInterface
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * @var int
     */
    private $inputMessageChunkLength;

    /**
     * @param resource $resource
     * @param int $inputMessageChunkLength
     */
    public function __construct($resource, int $inputMessageChunkLength)
    {
        $this->resource = $resource;
        $this->inputMessageChunkLength = $inputMessageChunkLength;
    }

    /**
     * {@inheritdoc}
     */
    public function read(): string
    {
        return trim(socket_read($this->resource, $this->inputMessageChunkLength));
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $message): void
    {
        socket_write($this->resource, $message, \strlen($message));
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        socket_close($this->resource);
    }

    /**
     * @return void
     */
    public function setBlockMode(): void
    {
        socket_set_block($this->resource);
    }
}
