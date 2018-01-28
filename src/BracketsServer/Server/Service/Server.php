<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop\ServerLoopInterface;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\RunningWorkerPool\RunningWorkerPoolInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class Server implements ServerInterface
{
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
     * @var ServerLoopInterface
     */
    private $loop;

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
        $this->createSocket();

        $this->createRunningWorkerPool();
        $this->setRunningWorkerPoolCapacity();

        $this->createLoop();
        $this->loop
            ->setSocket($this->socket)
            ->setWorker($this->createWorker());
        $this->loop->run();
    }

    /**
     * {@inheritdoc}
     */
    public function reload(): void
    {
        $this->closeUnusedSocket();
        $this->createSocket();

        $this->setRunningWorkerPoolCapacity();

        $this->loop
            ->setSocket($this->socket)
            ->setWorker($this->createWorker());
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        $this->closeUnusedSocket();
        $this->runningWorkerPool->terminateAll();
        $this->loop->stop();
    }

    /**
     * @return void
     */
    private function createLoop(): void
    {
        $this->loop = $this->container->get('server_loop');
    }

    /**
     * @return void
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function createSocket(): void
    {
        $this->socket = $this->container->get('socket');
        $this->socket->create();
        $this->socket->bind();
    }

    /**
     * @return void
     */
    private function closeUnusedSocket(): void
    {
        if (!$this->runningWorkerPool->isUsingSocket($this->socket)) {
            $this->socket->close();
        }
    }

    /**
     * @return void
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function createRunningWorkerPool(): void
    {
        $this->runningWorkerPool = $this->container->get('running_worker_pool');
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    private function setRunningWorkerPoolCapacity(): void
    {
        $this->runningWorkerPool->setCapacity(
            $this->container->getParameter('max_open_connections')
        );
    }

    /**
     * @return WorkerInterface
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function createWorker(): WorkerInterface
    {
        return $this->container->get('worker');
    }
}
