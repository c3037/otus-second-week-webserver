<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

use ArrayObject;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ConnectionAcceptorThread;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ZombieKillerThread;
use Psr\Container\ContainerInterface;
use Thread;
use Volatile;

final class Server implements ServerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ArrayObject|Thread[]
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

        $thread = new ConnectionAcceptorThread($workerList);
        $thread->setContainer($this->container);
        $thread->setAutoloaders(spl_autoload_functions());
        $thread->start();
        $this->threadList->append($thread);

        $thread = new ZombieKillerThread($workerList);
        $thread->start();
        $this->threadList->append($thread);
    }

    /**
     * {@inheritdoc}
     */
    public function interruptHandler(): void
    {
        printf('%sInterrupting server...%s', PHP_EOL, PHP_EOL);
        foreach ($this->threadList as $thread) {
            $thread->terminate();
        }
        exit;
    }
}
