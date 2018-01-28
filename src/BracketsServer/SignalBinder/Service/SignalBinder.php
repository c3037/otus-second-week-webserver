<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\SignalBinder\Service;

final class SignalBinder implements SignalBinderInterface
{
    /**
     * @var int[]
     */
    private $signalList = [];

    /**
     * {@inheritdoc}
     */
    public function setAsyncMode(): void
    {
        pcntl_async_signals(true);
    }

    /**
     * {@inheritdoc}
     */
    public function bind(int $signalNumber, $handler): void
    {
        pcntl_signal($signalNumber, $handler);

        $this->signalList[] = $signalNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(): void
    {
        pcntl_signal_dispatch();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        foreach ($this->signalList as $signal) {
            pcntl_signal($signal, SIG_DFL);
        }
    }
}
