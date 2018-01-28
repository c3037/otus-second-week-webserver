<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\BindParamsDeterminator\BindParamsDeterminatorInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\Factory\SocketConnectionFactoryInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnection;
use Threaded;

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
     * @var SocketConnectionFactoryInterface
     */
    private $connectionFactory;

    /**
     * @var BindParamsDeterminatorInterface
     */
    private $bindParamsDeterminator;

    /**
     * @param int $backlogSize
     * @param SocketConnectionFactoryInterface $connectionFactory
     * @param BindParamsDeterminatorInterface $bindParamsDeterminator
     */
    public function __construct(
        int $backlogSize,
        SocketConnectionFactoryInterface $connectionFactory,
        BindParamsDeterminatorInterface $bindParamsDeterminator
    ) {
        $this->backlogSize = $backlogSize;
        $this->connectionFactory = $connectionFactory;
        $this->bindParamsDeterminator = $bindParamsDeterminator;

        $this->resource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function bind(): void
    {
        $bindParams = $this->bindParamsDeterminator->determine();

        socket_bind($this->resource, $bindParams->getHost(), $bindParams->getPort());
        socket_listen($this->resource, $this->backlogSize);
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
