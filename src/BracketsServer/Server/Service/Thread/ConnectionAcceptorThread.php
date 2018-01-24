<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerPoolInterface;
use Thread;

final class ConnectionAcceptorThread extends Thread implements ThreadInterface
{
    /**
     * @var WorkerPoolInterface
     */
    private $workerPool;

    /**
     * @var bool
     */
    private $terminateSignal = false;

    /**
     * @var array
     */
    private $autoloaders;

    /**
     * @var SocketInterface
     */
    private $socket;

    /**
     * @var WorkerInterface
     */
    private $subProcessWorker;

    /**
     * @param WorkerPoolInterface $workerPool
     */
    public function __construct(WorkerPoolInterface $workerPool)
    {
        $this->workerPool = $workerPool;
    }

    /**
     * @param array $autoloaders
     * @return void
     */
    public function setAutoloaders(array $autoloaders): void
    {
        $this->autoloaders = $autoloaders;
    }

    /**
     * @param SocketInterface $socket
     * @return void
     */
    public function setSocket(SocketInterface $socket): void
    {
        $this->socket = $socket;
    }

    /**
     * @param WorkerInterface $subProcessWorker
     * @return void
     */
    public function setSubProcessWorker(WorkerInterface $subProcessWorker): void
    {
        $this->subProcessWorker = $subProcessWorker;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->bindAutoloaders();

        $this->runWaitConnectionLoop();
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        $this->terminateSignal = true;
    }

    /**
     * @return void
     */
    private function bindAutoloaders(): void
    {
        foreach ($this->autoloaders as $autoLoader) {
            spl_autoload_register([$autoLoader[0], $autoLoader[1]]);
        }
    }

    /**
     * @return void
     */
    private function runWaitConnectionLoop(): void
    {
        while (true) {
            if ($this->hasTerminateSignal()) {
                break;
            }

            if ($this->canAcceptNewConnections()
                && $clientConnection = $this->socket->acceptConnection()) {

                $workerPid = pcntl_fork();
                if (empty($workerPid)) {
                    $this->subProcessWorker->handle($clientConnection);
                    break;
                }

                $this->addWorkerToPool($workerPid);
                unset($clientConnection);
            }
            usleep(200000);
        }
    }

    /**
     * @return bool
     */
    private function hasTerminateSignal(): bool
    {
        return $this->terminateSignal;
    }

    /**
     * @return bool
     */
    private function canAcceptNewConnections(): bool
    {
        return !$this->workerPool->isFull();
    }

    /**
     * @param int $workerPid
     * @return void
     */
    private function addWorkerToPool(int $workerPid): void
    {
        $this->synchronized(function () use ($workerPid) {
            $this->workerPool[$workerPid] = $workerPid;
        });
    }
}
