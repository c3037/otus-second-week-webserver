<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop;

use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop\Task\LoopTaskInterface;

interface ServerLoopInterface
{
    /**
     * @param LoopTaskInterface $loopTask
     * @return ServerLoopInterface
     */
    public function addTask(LoopTaskInterface $loopTask): ServerLoopInterface;

    /**
     * @return void
     */
    public function cleanTasks(): void;

    /**
     * @return void
     */
    public function run(): void;

    /**
     * @return void
     */
    public function stop(): void;
}
