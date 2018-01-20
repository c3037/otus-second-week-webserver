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
    private $synchronizedData;

    /**
     * @param Volatile $synchronizedData
     */
    public function __construct(Volatile $synchronizedData)
    {
        $this->synchronizedData = $synchronizedData;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $workerList =& $this->synchronizedData['workerList'];
        /** @var int[] $workerList */

        while (true) {
            foreach ($workerList as $k => $worker) {
                if (pcntl_waitpid($worker, $status, WNOHANG) <= 0) {
                    continue;
                }
                $this->synchronized(function () use (&$workerList, $k) {
                    unset($workerList[$k]);
                });
            }
            sleep(1);
        }
    }
}
