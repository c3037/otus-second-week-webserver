<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;

interface ServerLoopInterface
{
    /**
     * @param SocketInterface $socket
     * @return ServerLoopInterface
     */
    public function setSocket(SocketInterface $socket): ServerLoopInterface;

    /**
     * @param WorkerInterface $worker
     * @return ServerLoopInterface
     */
    public function setWorker(WorkerInterface $worker): ServerLoopInterface;

    /**
     * @return void
     */
    public function run(): void;

    /**
     * @return void
     */
    public function stop(): void;
}
