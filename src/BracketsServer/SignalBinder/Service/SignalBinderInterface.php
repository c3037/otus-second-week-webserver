<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\SignalBinder\Service;

interface SignalBinderInterface
{
    /**
     * @return void
     */
    public function setAsyncMode(): void;

    /**
     * @param int $signalNumber
     * @param callable|int $handler
     * @return mixed
     */
    public function bind(int $signalNumber, $handler): void;

    /**
     * @return void
     */
    public function dispatch(): void;

    /**
     * @return void
     */
    public function clear(): void;
}
