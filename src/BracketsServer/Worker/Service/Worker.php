<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Worker\Service;

final class Worker implements WorkerInterface
{
    /**
     * @var RequestProcessorInterface
     */
    private $requestProcessor;

    /**
     * @var string
     */
    private $quitCommand;

    /**
     * @var int
     */
    private $maxInputMessageLength;

    /**
     * @param RequestProcessorInterface $requestProcessor
     * @param string $quitCommand
     * @param int $maxInputMessageLength
     */
    public function __construct(
        RequestProcessorInterface $requestProcessor,
        string $quitCommand,
        int $maxInputMessageLength
    ) {
        $this->requestProcessor = $requestProcessor;
        $this->quitCommand = $quitCommand;
        $this->maxInputMessageLength = $maxInputMessageLength;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($clientConnection): void
    {
        $this->setBlockMode($clientConnection);
        $this->printWelcomeMessage($clientConnection);

        while (true) {
            $request = $this->readInput($clientConnection);

            if ($this->isQuitCommand($request)) {
                $this->closeConnection($clientConnection);
                break;
            }

            $this->printMessage($clientConnection, $this->requestProcessor->process($request));
        }
    }

    /**
     * @param resource $clientConnection
     * @return void
     */
    private function setBlockMode($clientConnection): void
    {
        socket_set_block($clientConnection);
    }

    /**
     * @param resource $clientConnection
     * @return void
     */
    private function printWelcomeMessage($clientConnection): void
    {
        $message =
            sprintf(
                "Welcome to server.%sTo quit, type '%s'.%s",
                PHP_EOL,
                $this->quitCommand,
                PHP_EOL
            );

        $this->printMessage($clientConnection, $message);
    }

    /**
     * @param resource $clientConnection
     * @param string $message
     * @return void
     */
    private function printMessage($clientConnection, string $message): void
    {
        socket_write($clientConnection, $message, \strlen($message));
    }

    /**
     * @param resource $clientConnection
     * @return string
     */
    private function readInput($clientConnection): string
    {
        return trim(socket_read($clientConnection, $this->maxInputMessageLength));
    }

    /**
     * @param string $request
     * @return bool
     */
    private function isQuitCommand(string $request): bool
    {
        return $this->quitCommand === $request;
    }

    /**
     * @param resource $clientConnection
     * @return void
     */
    private function closeConnection($clientConnection): void
    {
        socket_close($clientConnection);
    }
}
