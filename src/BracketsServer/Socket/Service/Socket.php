<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Dto\BindParams;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\Factory\SocketConnectionFactoryInterface;

final class Socket implements SocketInterface
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * @var int
     */
    private $backlogSize;

    /**
     * @var BindParams
     */
    private $bindParams;

    /**
     * @var SocketConnectionFactoryInterface
     */
    private $connectionFactory;

    /**
     * @param int $backlogSize
     * @param SocketConnectionFactoryInterface $connectionFactory
     */
    public function __construct(int $backlogSize, SocketConnectionFactoryInterface $connectionFactory)
    {
        $this->backlogSize = $backlogSize;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(): void
    {
        $this->resource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function bind(BindParams $bindParams): void
    {
        socket_bind($this->resource, $bindParams->getHost(), $bindParams->getPort());
        socket_listen($this->resource, $this->backlogSize);

        $this->bindParams = $bindParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getBindParams(): BindParams
    {
        return $this->bindParams;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptConnection()
    {
        $connectionResource = socket_accept($this->resource);

        if ($connectionResource) {
            return $this->connectionFactory->spawn($connectionResource);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        socket_close($this->resource);
    }
}
