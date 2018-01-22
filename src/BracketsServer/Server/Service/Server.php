<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

use ArrayObject;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ConnectionAcceptorThread;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ThreadInterface;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ZombieKillerThread;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Volatile;

final class Server implements ServerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ArrayObject|ThreadInterface[]
     */
    private $threadList;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->threadList = new ArrayObject();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $workerList = new Volatile();

        $this->startConnectionAcceptorThread($workerList);
        $this->startZombieKillerThread($workerList);
    }

    /**
     * {@inheritdoc}
     */
    public function interruptHandler(): void
    {
        $this->terminateThreads();
    }

    /**
     * @param Volatile $workerList
     * @return void
     * @throws ContainerExceptionInterface
     */
    private function startConnectionAcceptorThread(Volatile $workerList): void
    {
        $thread = new ConnectionAcceptorThread($workerList);
        $thread->setAutoloaders(spl_autoload_functions());
        $thread->setSocket($this->createSocket());
        $thread->setSubProcessWorker($this->createSubProcessWorker());
        $thread->start();
        $this->threadList->append($thread);
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
     * @return WorkerInterface
     * @throws ContainerExceptionInterface
     */
    private function createSubProcessWorker(): WorkerInterface
    {
        return $this->container->get('worker');
    }

    /**
     * @param Volatile $workerList
     * @return void
     */
    private function startZombieKillerThread(Volatile $workerList): void
    {
        $thread = new ZombieKillerThread($workerList);
        $thread->start();
        $this->threadList->append($thread);
    }

    /**
     * @return void
     */
    private function terminateThreads(): void
    {
        foreach ($this->threadList as $thread) {
            $thread->terminate();
        }
    }
}
