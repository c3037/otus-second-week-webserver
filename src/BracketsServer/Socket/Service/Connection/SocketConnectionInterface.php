<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection;

interface SocketConnectionInterface
{
    /**
     * @return string
     */
    public function read(): string;

    /**
     * @param string $message
     * @return void
     */
    public function write(string $message): void;

    /**
     * @return void
     */
    public function close(): void;
}
