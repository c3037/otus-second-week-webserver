<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\WorkerList;

use Countable;
use Traversable;

interface WorkerListInterface extends Countable, Traversable
{
    /**
     * @return bool
     */
    public function isFull(): bool;
}
