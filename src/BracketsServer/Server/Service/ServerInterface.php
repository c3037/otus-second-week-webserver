<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

use ArrayAccess;

interface ServerInterface
{
    /**
     * @param ArrayAccess $threadList
     */
    public function run(ArrayAccess &$threadList): void;

    /**
     * @param ArrayAccess $threadList
     */
    public function interruptHandler(ArrayAccess &$threadList): void;
}
