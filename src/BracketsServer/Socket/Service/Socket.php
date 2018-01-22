<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service;

use Threaded;

final class Socket extends Threaded implements SocketInterface
{
    /**
     * @var resource
     */
    private $socket;

    /**
     * @var BindParamsDeterminatorInterface
     */
    private $bindParamsDeterminator;

    /**
     * @var int
     */
    private $maxConcurrentConnections;

    /**
     * @param BindParamsDeterminatorInterface $bindParamsDeterminator
     * @param int $maxConcurrentConnections
     */
    public function __construct(BindParamsDeterminatorInterface $bindParamsDeterminator, int $maxConcurrentConnections)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        $this->bindParamsDeterminator = $bindParamsDeterminator;
        $this->maxConcurrentConnections = $maxConcurrentConnections;
    }

    /**
     * {@inheritdoc}
     */
    public function bind(): void
    {
        $bindParams = $this->bindParamsDeterminator->determine();

        socket_bind($this->socket, $bindParams->getHost(), $bindParams->getPort());
        socket_listen($this->socket, $this->maxConcurrentConnections);
        socket_set_nonblock($this->socket);
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        socket_close($this->socket);
    }

    /**
     * {@inheritdoc}
     */
    public function acceptConnection()
    {
        return socket_accept($this->socket);
    }
}
