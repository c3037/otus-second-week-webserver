<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\Factory;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnectionInterface;

interface SocketConnectionFactoryInterface
{
    /**
     * @param resource $resource
     * @return SocketConnectionInterface
     */
    public function spawn($resource): SocketConnectionInterface;
}
