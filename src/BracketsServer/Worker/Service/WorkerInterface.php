<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Worker\Service;

interface WorkerInterface
{
    /**
     * @param resource $clientConnection
     * @return void
     */
    public function handle($clientConnection): void;
}
