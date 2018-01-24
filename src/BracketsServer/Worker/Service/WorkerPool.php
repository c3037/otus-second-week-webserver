<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Worker\Service;

use Volatile;

/**
 * @method int count
 * @method mixed current
 * @method void next
 * @method mixed key
 * @method bool valid
 * @method void rewind
 */
final class WorkerPool extends Volatile implements WorkerPoolInterface
{
    /**
     * @var int
     */
    private $capacity;

    /**
     * @param int $maxOpenConnections
     */
    public function __construct(int $maxOpenConnections)
    {
        $this->capacity = $maxOpenConnections;
    }

    /**
     * {@inheritdoc}
     */
    public function isFull(): bool
    {
        return ($this->count() - 1) >= $this->capacity;
    }
}
