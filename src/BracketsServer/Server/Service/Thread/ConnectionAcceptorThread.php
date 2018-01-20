<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\SocketInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\WorkerInterface;
use Thread;
use Volatile;

final class ConnectionAcceptorThread extends Thread
{
    /**
     * @var Volatile
     */
    private $synchronizedData;

    /**
     * @param Volatile $synchronizedData
     */
    public function __construct(Volatile $synchronizedData)
    {
        $this->synchronizedData = $synchronizedData;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->bindAutoloaders();

        $socket = $this->createSocket();

        $this->waitConnections($socket);
    }

    /**
     * @param SocketInterface $socket
     */
    private function waitConnections(SocketInterface $socket): void
    {
        while (true) {
            $clientConnection = $socket->getConnection();

            $newWorkerPid = pcntl_fork();
            if (empty($newWorkerPid)) {
                $this->getWorkerPrototype()->run($clientConnection);
                break;
            }

            $this->synchronized(function () use ($newWorkerPid) {
                $this->synchronizedData['workerList'][] = $newWorkerPid;
            });
            unset($clientConnection);
        }
    }

    /**
     * @return void
     */
    private function bindAutoloaders(): void
    {
        $autoLoaders = $this->synchronizedData['autoload'];
        /** @var mixed[] $autoLoaders */

        foreach ($autoLoaders as $autoLoader) {
            spl_autoload_register([$autoLoader[0], $autoLoader[1]]);
        }
    }

    /**
     * @return SocketInterface
     */
    private function createSocket(): SocketInterface
    {
        $socket = $this->synchronizedData['container']->get('socket');
        $socket->bind();

        return $socket;
    }

    /**
     * @return WorkerInterface
     */
    private function getWorkerPrototype(): WorkerInterface
    {
        return $this->synchronizedData['container']->get('worker');
    }
}
