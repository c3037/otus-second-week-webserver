<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\RunningWorkerPool;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use IteratorAggregate;

interface RunningWorkerPoolInterface extends IteratorAggregate
{
    /**
     * @param int $capacity
     * @return RunningWorkerPoolInterface
     */
    public function setCapacity(int $capacity): RunningWorkerPoolInterface;

    /**
     * @return bool
     */
    public function isFull(): bool;

    /**
     * @param int $workerPid
     * @param SocketInterface $socket
     * @return void
     */
    public function add(int $workerPid, SocketInterface $socket): void;

    /**
     * @param int $workerPid
     * @return void
     */
    public function drop(int $workerPid): void;

    /**
     * @param SocketInterface $socket
     * @return bool
     */
    public function isUsingSocket(SocketInterface $socket): bool;
}
