<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Server\Service;

use ArrayAccess;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ConnectionAcceptorThread;
use c3037\Otus\SecondWeek\BracketsServer\Server\Service\Thread\ZombieKillerThread;
use Psr\Container\ContainerInterface;
use Volatile;

final class Server implements ServerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
    public function run(ArrayAccess &$threadList): void
    {
        $synchronizedData = new Volatile();
        $synchronizedData['workerList'] = [];
        $synchronizedData['container'] = $this->container;
        $synchronizedData['autoload'] = spl_autoload_functions();

        $thread = new ConnectionAcceptorThread($synchronizedData);
        $thread->start();
        $threadList[] = $thread;

        $thread = new ZombieKillerThread($synchronizedData);
        $thread->start();
        $threadList[] = $thread;
    }

    /**
     * {@inheritdoc}
     */
    public function interruptHandler(ArrayAccess &$threadList): void
    {
        printf('%sInterrupt handler...%s', PHP_EOL, PHP_EOL);
        exit;
    }
}
