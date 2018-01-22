<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread;

use c3037\Otus\SecondWeek\BracketsServer\Server\Service\WorkerList\WorkerListInterface;
use Thread;

final class ZombieKillerThread extends Thread implements ThreadInterface
{
    /**
     * @var WorkerListInterface
     */
    private $workerList;

    /**
     * @var bool
     */
    private $terminateSignal = false;

    /**
     * @param WorkerListInterface $workerList
     */
    public function __construct(WorkerListInterface $workerList)
    {
        $this->workerList = $workerList;
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
        foreach ($this->workerList as $workerPid) {
            if (pcntl_waitpid($workerPid, $status, WNOHANG) <= 0) {
                continue;
            }
            $this->dropWorkerFromList($workerPid);
        }
    }

    /**
     * @param int $workerPid
     * @return void
     */
    private function dropWorkerFromList(int $workerPid): void
    {
        $this->synchronized(function () use ($workerPid) {
            unset($this->workerList[$workerPid]);
        });
    }
}
