<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop;

use c3037\Otus\SecondWeek\BracketsServer\Server\Exception\BreakLoopException;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\RunningWorkerPool\RunningWorkerPoolInterface;
use c3037\Otus\SecondWeek\BracketsServer\SignalBinder\Service\SignalBinderInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnectionInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;

final class ServerLoop implements ServerLoopInterface
{
    private const SLEEP_INTERVAL = 200000;

    /**
     * @var bool
     */
    private $stopSignal = false;

    /**
     * @var RunningWorkerPoolInterface
     */
    private $runningWorkerPool;

    /**
     * @var SignalBinderInterface
     */
    private $signalBinder;

    /**
     * @var WorkerInterface
     */
    private $worker;

    /**
     * @var SocketInterface
     */
    private $socket;

    /**
     * @param RunningWorkerPoolInterface $runningWorkerPool
     * @param SignalBinderInterface $signalBinder
     */
    public function __construct(
        RunningWorkerPoolInterface $runningWorkerPool,
        SignalBinderInterface $signalBinder
    ) {
        $this->runningWorkerPool = $runningWorkerPool;
        $this->signalBinder = $signalBinder;
    }

    /**
     * {@inheritdoc}
     */
    public function setWorker(WorkerInterface $worker): ServerLoopInterface
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSocket(SocketInterface $socket): ServerLoopInterface
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        try {
            $this->runLoop();
        } catch (BreakLoopException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        $this->stopSignal = true;
    }

    /**
     * @return void
     * @throws BreakLoopException
     */
    private function runLoop(): void
    {
        while (true) {
            if ($this->stopSignal) {
                throw new BreakLoopException('Has stop signal');
            }

            if ($this->canAcceptNewConnections()) {
                $this->checkNewConnections();
            }

            $this->collectGarbage();

            usleep(self::SLEEP_INTERVAL);
        }
    }

    /**
     * @return bool
     */
    private function canAcceptNewConnections(): bool
    {
        return !$this->runningWorkerPool->isFull();
    }

    /**
     * @return void
     * @throws BreakLoopException
     */
    private function checkNewConnections(): void
    {
        if ($clientConnection = $this->socket->acceptConnection()) {

            $workerPid = pcntl_fork();
            if (empty($workerPid)) {
                $this->processNewConnection($clientConnection);
            }

            $this->runningWorkerPool->add($workerPid, $this->socket);
        }
    }

    /**
     * @param SocketConnectionInterface $clientConnection
     * @return void
     * @throws BreakLoopException
     */
    private function processNewConnection(SocketConnectionInterface $clientConnection): void
    {
        $this->signalBinder->clear();
        $this->runningWorkerPool->clear();
        $this->worker->handle($clientConnection);

        throw new BreakLoopException('Child process exit');
    }

    /**
     * @return void
     */
    private function collectGarbage(): void
    {
        foreach ($this->runningWorkerPool as $workerPid => $socket) {
            if (pcntl_waitpid($workerPid, $status, WNOHANG) <= 0) {
                continue;
            }

            $this->runningWorkerPool->drop($workerPid);

            if ($this->socket !== $socket) {
                $this->closeUnusedSocket($socket);
            }
        }
    }

    /**
     * @param SocketInterface $socket
     * @return void
     */
    private function closeUnusedSocket(SocketInterface $socket): void
    {
        if (!$this->runningWorkerPool->isUsingSocket($socket)) {
            $socket->close();
        }
    }
}
