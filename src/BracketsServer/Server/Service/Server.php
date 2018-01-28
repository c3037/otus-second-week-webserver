<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

use c3037\Otus\SecondWeek\BracketsServer\Server\Exeption\BreakLoopException;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\RunningWorkerPool\RunningWorkerPoolInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Server implements ServerInterface
{
    /**
     * @var bool
     */
    private $terminateLoop = false;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SocketInterface
     */
    private $socket;

    /**
     * @var RunningWorkerPoolInterface
     */
    private $runningWorkerPool;

    /**
     * @var WorkerInterface
     */
    private $worker;

    /**
     * @var int[]
     */
    private $bindedSignals;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->initializeFromContainer();

        try {
            $this->runMainLoop();
        } catch (BreakLoopException $e) {
        }

    }

    /**
     * {@inheritdoc}
     */
    public function reload(): void
    {
        $this->closeNotUsingSocket($this->socket);
        $this->initializeFromContainer();
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        foreach ($this->runningWorkerPool as $workerPid => $socket) {
            posix_kill($workerPid, SIGTERM);
        }
        $this->terminateLoop = true;
    }

    /**
     * {@inheritdoc}
     */
    public function bindSignalHandler(int $signalNumber, $handler): void
    {
        pcntl_async_signals(true);
        pcntl_signal($signalNumber, $handler);

        $this->bindedSignals[] = $signalNumber;
    }

    /**
     * @return void
     */
    private function initializeFromContainer(): void
    {
        $this->createSocket();
        $this->createWorker();
        $this->createRunningWorkerPool();
    }

    /**
     * @return void
     */
    private function createSocket(): void
    {
        $this->socket = $this->container->get('socket');
        $this->socket->bind();
    }

    /**
     * @return void
     */
    private function createWorker(): void
    {
        $this->worker = $this->container->get('worker');
    }

    /**
     * @return void
     */
    private function createRunningWorkerPool(): void
    {
        if (!$this->runningWorkerPool) {
            $this->runningWorkerPool = $this->container->get('running_worker_pool');
        }

        $this->runningWorkerPool->setCapacity(
            $this->container->getParameter('max_open_connections')
        );
    }

    /**
     * @return void
     * @throws BreakLoopException
     */
    private function runMainLoop(): void
    {
        while (true) {
            if ($this->terminateLoop) {
                throw new BreakLoopException('Has terminate signal');
            }

            if ($this->canAcceptNewConnections()) {
                $this->checkNewConnections();
            }

            $this->collectGarbage();

            usleep(200000);
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
                $this->restoreSignalHandlers();
                $this->worker->handle($clientConnection);
                throw new BreakLoopException('Child process exit');
            }

            $this->runningWorkerPool->add($workerPid, $this->socket);
        }
    }

    /**
     * @return void
     */
    private function restoreSignalHandlers(): void
    {
        foreach ($this->bindedSignals as $bindedSignal) {
            pcntl_signal($bindedSignal, SIG_DFL);
        }
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
                $this->closeNotUsingSocket($socket);
            }
        }
    }

    /**
     * @param SocketInterface $socket
     * @return void
     */
    private function closeNotUsingSocket(SocketInterface $socket): void
    {
        if (!$this->runningWorkerPool->isUsingSocket($socket)) {
            $socket->close();
        }
    }
}
