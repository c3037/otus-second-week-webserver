<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

use ArrayObject;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ConnectionAcceptorThread;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ThreadInterface;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ZombieKillerThread;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerPoolInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class Server implements ServerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ArrayObject|ThreadInterface[]
     */
    private $threadPool;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->threadPool = new ArrayObject();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $workerPool = $this->createWorkerPool();

        $this->startConnectionAcceptorThread($workerPool);
        $this->startZombieKillerThread($workerPool);
    }

    /**
     * {@inheritdoc}
     */
    public function interruptHandler(): void
    {
        $this->terminateThreads();
    }

    /**
     * @return WorkerPoolInterface
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function createWorkerPool(): WorkerPoolInterface
    {
        return $this->container->get('worker_pool');
    }

    /**
     * @param WorkerPoolInterface $workerPool
     * @return void
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function startConnectionAcceptorThread(WorkerPoolInterface $workerPool): void
    {
        $thread = new ConnectionAcceptorThread($workerPool);
        $thread->setAutoloaders(spl_autoload_functions());
        $thread->setSocket($this->createSocket());
        $thread->setSubProcessWorker($this->createSubProcessWorker());
        $thread->start();
        $this->threadPool->append($thread);
    }

    /**
     * @return SocketInterface
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function createSocket(): SocketInterface
    {
        $socket = $this->container->get('socket');
        $socket->bind();

        return $socket;
    }

    /**
     * @return WorkerInterface
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function createSubProcessWorker(): WorkerInterface
    {
        return $this->container->get('worker');
    }

    /**
     * @param WorkerPoolInterface $workerPool
     * @return void
     */
    private function startZombieKillerThread(WorkerPoolInterface $workerPool): void
    {
        $thread = new ZombieKillerThread($workerPool);
        $thread->start();
        $this->threadPool->append($thread);
    }

    /**
     * @return void
     */
    private function terminateThreads(): void
    {
        foreach ($this->threadPool as $thread) {
            $thread->terminate();
        }
    }
}
