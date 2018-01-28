<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\RunningWorkerPool;

use ArrayIterator;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use Iterator;

final class RunningWorkerPool implements RunningWorkerPoolInterface
{
    /**
     * @var int
     */
    private $capacity;

    /**
     * @var SocketInterface[]
     */
    private $pool = [];

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->pool);
    }

    /**
     * {@inheritdoc}
     */
    public function setCapacity(int $capacity): RunningWorkerPoolInterface
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isFull(): bool
    {
        return \count($this->pool) >= $this->capacity;
    }

    /**
     * {@inheritdoc}
     */
    public function add(int $workerPid, SocketInterface $socket): void
    {
        $this->pool[$workerPid] = $socket;
    }

    /**
     * {@inheritdoc}
     */
    public function drop(int $workerPid): void
    {
        unset($this->pool[$workerPid]);
    }

    /**
     * {@inheritdoc}
     */
    public function isUsingSocket(SocketInterface $socket): bool
    {
        return \in_array($socket, $this->pool);
    }
}
