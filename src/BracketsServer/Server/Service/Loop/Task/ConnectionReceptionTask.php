<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop\Task;

use c3037\Otus\SecondWeek\BracketsServer\Server\Exception\BreakLoopException;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\RunningWorkerPool\RunningWorkerPoolInterface;
use c3037\Otus\SecondWeek\BracketsServer\SignalBinder\Service\SignalBinderInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnectionInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;

final class ConnectionReceptionTask implements LoopTaskInterface
{
    /**
     * @var RunningWorkerPoolInterface
     */
    private $runningWorkerPool;

    /**
     * @var SignalBinderInterface
     */
    private $signalBinder;

    /**
     * @var SocketInterface
     */
    private $socket;

    /**
     * @var WorkerInterface
     */
    private $worker;

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
     * @param SocketInterface $socket
     * @return ConnectionReceptionTask
     */
    public function setSocket(SocketInterface $socket): ConnectionReceptionTask
    {
        $this->socket = $socket;

        return $this;
    }

    /**
     * @param WorkerInterface $worker
     * @return ConnectionReceptionTask
     */
    public function setWorker(WorkerInterface $worker): ConnectionReceptionTask
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        if ($this->runningWorkerPool->isFull()) {
            return;
        }

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
}
