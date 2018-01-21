<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Thread;
use Volatile;

final class ConnectionAcceptorThread extends Thread implements ThreadInterface
{
    /**
     * @var Volatile
     */
    private $workerList;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $autoloaders;

    /**
     * @var bool
     */
    private $terminateSignal = false;

    /**
     * @param Volatile $workerList
     */
    public function __construct(Volatile $workerList)
    {
        $this->workerList = $workerList;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @param array $autoloaders
     */
    public function setAutoloaders(array $autoloaders): void
    {
        $this->autoloaders = $autoloaders;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->bindAutoloaders();

        try {
            $this->waitConnections($this->createSocket());
        } catch (ContainerExceptionInterface $e) {
        }
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
     * @return SocketInterface
     * @throws ContainerExceptionInterface
     */
    private function createSocket(): SocketInterface
    {
        $socket = $this->container->get('socket');
        $socket->bind();

        return $socket;
    }

    /**
     * @param SocketInterface $socket
     * @throws ContainerExceptionInterface
     */
    private function waitConnections(SocketInterface $socket): void
    {
        while (true) {
            if ($this->hasTerminateSignal()) {
                break;
            }

            $newConnection = $socket->getNewConnection();
            if ($newConnection) {

                $workerPid = pcntl_fork();
                if (empty($workerPid)) {
                    $this->getWorkerPrototype()->communicate($newConnection);
                    break;
                }

                $this->addWorkerToList($workerPid);
                unset($newConnection);

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
     * @return WorkerInterface
     * @throws ContainerExceptionInterface
     */
    private function getWorkerPrototype(): WorkerInterface
    {
        return $this->container->get('worker');
    }

    /**
     * @param int $workerPid
     */
    private function addWorkerToList(int $workerPid): void
    {
        $this->synchronized(function () use ($workerPid) {
            $this->workerList[$workerPid] = $workerPid;
        });
    }
}
