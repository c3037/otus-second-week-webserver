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
            foreach ($this->workerList as $k => $worker) {
                if (pcntl_waitpid($worker, $status, WNOHANG) <= 0) {
                    continue;
                }
                $this->synchronized(function () use ($k) {
                    unset($this->workerList[$k]);
                });
            }
            sleep(1);
        }
    }
}
