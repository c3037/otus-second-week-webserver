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
    public function reload(): void;

    /**
     * @return void
     */
    public function terminate(): void;

    /**
     * @param int $signalNumber
     * @param callable|int $handler
     * @return void
     */
    public function bindSignalHandler(int $signalNumber, $handler): void;
}
