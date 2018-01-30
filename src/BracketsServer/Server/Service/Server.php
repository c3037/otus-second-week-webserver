<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop\ServerLoopInterface;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Loop\Task\LoopTaskInterface;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\RunningWorkerPool\RunningWorkerPoolInterface;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Dto\BindParams;
use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\BindParamsDeterminator\BindParamsDeterminatorInterface;
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
            ->addTask($this->createConnectionReceptionTask())
            ->addTask($this->createGarbageCollectionTask());
        $this->loop->run();
    }

    /**
     * {@inheritdoc}
     */
    public function reload(): void
    {
        if ($this->shouldSocketRebind()) {
            $this->closeSocketWithoutConnections();
            $this->createSocket();
        }

        $this->setRunningWorkerPoolCapacity();

        $this->loop->cleanTasks();
        $this->loop
            ->addTask($this->createConnectionReceptionTask())
            ->addTask($this->createGarbageCollectionTask());
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        $this->runningWorkerPool->terminateAll();
        $this->loop->stop();
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
        $this->socket->bind($this->getSocketBindParams());
    }

    /**
     * @return BindParams
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function getSocketBindParams(): BindParams
    {
        $bindParamsDeterminator = $this->container->get('bind_params_determinator');
        /** @var BindParamsDeterminatorInterface $bindParamsDeterminator */

        return $bindParamsDeterminator->determine();
    }

    /**
     * @return bool
     */
    private function shouldSocketRebind(): bool
    {
        return $this->socket->getBindParams() != $this->getSocketBindParams();
    }

    /**
     * @return void
     */
    private function closeSocketWithoutConnections(): void
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
     * @return void
     */
    private function createLoop(): void
    {
        $this->loop = $this->container->get('server_loop');
    }

    /**
     * @return LoopTaskInterface
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function createConnectionReceptionTask(): LoopTaskInterface
    {
        $task = $this->container->get('connection_reception_task');
        $task
            ->setSocket($this->socket)
            ->setWorker($this->createWorker());

        return $task;
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

    /**
     * @return LoopTaskInterface
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    private function createGarbageCollectionTask(): LoopTaskInterface
    {
        $task = $this->container->get('garbage_collection_task');
        $task->setSocket($this->socket);

        return $task;
    }
}
