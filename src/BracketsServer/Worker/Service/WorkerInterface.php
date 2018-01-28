<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Worker\Service;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnectionInterface;

interface WorkerInterface
{
    /**
     * @param SocketConnectionInterface $connection
     * @return void
     */
    public function handle(SocketConnectionInterface $connection): void;
}
