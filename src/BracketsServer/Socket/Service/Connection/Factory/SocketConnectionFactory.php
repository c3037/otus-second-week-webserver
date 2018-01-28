<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\Factory;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnection;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnectionInterface;

final class SocketConnectionFactory implements SocketConnectionFactoryInterface
{
    /**
     * @var int
     */
    private $inputMessageChunkLength;

    /**
     * @param int $inputMessageChunkLength
     */
    public function __construct(int $inputMessageChunkLength)
    {
        $this->inputMessageChunkLength = $inputMessageChunkLength;
    }

    /**
     * {@inheritdoc}
     */
    public function spawn($resource): SocketConnectionInterface
    {
        $connection = new SocketConnection($resource, $this->inputMessageChunkLength);
        $connection->setBlockMode();

        return $connection;
    }
}
