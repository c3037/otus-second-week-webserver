<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Worker\Service;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnectionInterface;
use c3037\Otus\SecondWeek\BracketsServer\Worker\Service\RequestProcessor\RequestProcessorInterface;

final class Worker implements WorkerInterface
{
    private const WELCOME_MESSAGE = "Welcome to socket server.%sTo quit, type '%s'.%s";

    private const BYE_MESSAGE = 'Bye!%s';

    /**
     * @var RequestProcessorInterface
     */
    private $requestProcessor;

    /**
     * @var string
     */
    private $quitCommand;

    /**
     * @param RequestProcessorInterface $requestProcessor
     * @param string $quitCommand
     */
    public function __construct(RequestProcessorInterface $requestProcessor, string $quitCommand)
    {
        $this->requestProcessor = $requestProcessor;
        $this->quitCommand = $quitCommand;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(SocketConnectionInterface $connection): void
    {
        $this->printWelcomeMessage($connection);

        while (true) {
            $request = $connection->read();

            if ($this->isQuitCommand($request)) {
                $this->printByeMessage($connection);
                $connection->close();
                break;
            }

            $connection->write($this->requestProcessor->process($request));
        }
    }

    /**
     * @param SocketConnectionInterface $clientConnection
     * @return void
     */
    private function printWelcomeMessage(SocketConnectionInterface $clientConnection): void
    {
        $message = sprintf(self::WELCOME_MESSAGE, PHP_EOL, $this->quitCommand, PHP_EOL);

        $clientConnection->write($message);
    }

    /**
     * @param SocketConnectionInterface $clientConnection
     * @return void
     */
    private function printByeMessage(SocketConnectionInterface $clientConnection): void
    {
        $message = sprintf(self::BYE_MESSAGE, PHP_EOL);

        $clientConnection->write($message);
    }

    /**
     * @param string $request
     * @return bool
     */
    private function isQuitCommand(string $request): bool
    {
        return $this->quitCommand === $request;
    }
}
