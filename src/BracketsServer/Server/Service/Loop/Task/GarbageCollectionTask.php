<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop\Task;

use c3037\Otus\SecondWeek\BracketsServer\Server\Service\RunningWorkerPool\RunningWorkerPoolInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;

final class GarbageCollectionTask implements LoopTaskInterface
{
    /**
     * @var RunningWorkerPoolInterface
     */
    private $runningWorkerPool;

    /**
     * @var SocketInterface
     */
    private $socket;

    /**
     * @param RunningWorkerPoolInterface $runningWorkerPool
     */
    public function __construct(RunningWorkerPoolInterface $runningWorkerPool)
    {
        $this->runningWorkerPool = $runningWorkerPool;
    }

    /**
     * @param SocketInterface $socket
     * @return GarbageCollectionTask
     */
    public function setSocket(SocketInterface $socket): GarbageCollectionTask
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        foreach ($this->runningWorkerPool as $workerPid => $socket) {
            if (pcntl_waitpid($workerPid, $status, WNOHANG) <= 0) {
                continue;
            }

            $this->runningWorkerPool->drop($workerPid);

            if ($this->socket !== $socket) {
                $this->closeUnusedSocket($socket);
            }
        }
    }

    /**
     * @param SocketInterface $socket
     * @return void
     */
    private function closeUnusedSocket(SocketInterface $socket): void
    {
        if (!$this->runningWorkerPool->isUsingSocket($socket)) {
            $socket->close();
        }
    }
}
