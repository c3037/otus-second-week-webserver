<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread;

use Thread;
use Volatile;

final class ZombieKillerThread extends Thread
{
    /**
     * @var Volatile
     */
    private $workerList;

    /**
     * @var bool
     */
    private $terminateSignal = false;

    /**
     * @param Volatile $workerList
     */
    public function __construct(Volatile $workerList)
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
     * @return void
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
     */
    private function dropWorkerFromList(int $workerPid): void
    {
        $this->synchronized(function () use ($workerPid) {
            unset($this->workerList[$workerPid]);
        });
    }
}
