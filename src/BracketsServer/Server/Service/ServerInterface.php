<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

interface ServerInterface
{
    /**
     * @return void
     */
    public function run(): void;

    /**
     * @return void
     */
    public function terminate(): void;
}
