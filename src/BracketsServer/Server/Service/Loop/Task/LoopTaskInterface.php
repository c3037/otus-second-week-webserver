<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop\Task;

use c3037\Otus\SecondWeek\BracketsServer\Server\Exception\BreakLoopException;

interface LoopTaskInterface
{
    /**
     * @return void
     * @throws BreakLoopException
     */
    public function run(): void;
}
