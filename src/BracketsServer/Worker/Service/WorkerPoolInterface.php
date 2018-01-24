<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Worker\Service;

use Countable;
use Traversable;

interface WorkerPoolInterface extends Countable, Traversable
{
    /**
     * @return bool
     */
    public function isFull(): bool;
}
