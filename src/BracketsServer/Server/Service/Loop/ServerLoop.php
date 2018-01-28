<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop;

use c3037\Otus\SecondWeek\BracketsServer\Server\Exception\BreakLoopException;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop\Task\LoopTaskInterface;

final class ServerLoop implements ServerLoopInterface
{
    private const SLEEP_INTERVAL = 200000;

    /**
     * @var bool
     */
    private $stopSignal = false;

    /**
     * @var LoopTaskInterface[]
     */
    private $tasks;

    /**
     * {@inheritdoc}
     */
    public function addTask(LoopTaskInterface $loopTask): ServerLoopInterface
    {
        $this->tasks[] = $loopTask;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanTasks(): void
    {
        $this->tasks = [];
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        try {
            $this->runLoop();
        } catch (BreakLoopException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        $this->stopSignal = true;
    }

    /**
     * @return void
     * @throws BreakLoopException
     */
    private function runLoop(): void
    {
        while (true) {
            if ($this->stopSignal) {
                throw new BreakLoopException('Has stop signal');
            }

            foreach ($this->tasks as $task) {
                $task->run();
            }

            usleep(self::SLEEP_INTERVAL);
        }
    }
}
