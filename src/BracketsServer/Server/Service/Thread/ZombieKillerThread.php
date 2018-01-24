<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread;

use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerPoolInterface;
use Thread;

final class ZombieKillerThread extends Thread implements ThreadInterface
{
    /**
     * @var WorkerPoolInterface
     */
    private $workerPool;

    /**
     * @var bool
     */
    private $terminateSignal = false;

    /**
     * @param WorkerPoolInterface $workerPool
     */
    public function __construct(WorkerPoolInterface $workerPool)
    {
        $this->workerPool = $workerPool;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        while (true) {
            if ($this->hasTerminateSignal()) {
                break;
            }

            $this->readWorkerExitCodes();
            usleep(200000);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        $this->terminateSignal = true;
    }

    /**
     * @return bool
     */
    private function hasTerminateSignal(): bool
    {
        return $this->terminateSignal;
    }

    /**
     * @return void
     */
    private function readWorkerExitCodes(): void
    {
        foreach ($this->workerPool as $workerPid) {
            if (pcntl_waitpid($workerPid, $status, WNOHANG) <= 0) {
                continue;
            }
            $this->dropWorkerFromPool($workerPid);
        }
    }

    /**
     * @param int $workerPid
     * @return void
     */
    private function dropWorkerFromPool(int $workerPid): void
    {
        $this->synchronized(function () use ($workerPid) {
            unset($this->workerPool[$workerPid]);
        });
    }
}
