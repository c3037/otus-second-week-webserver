<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Service\Connection\SocketConnectionInterface;

interface SocketInterface
{
    /**
     * @return void
     */
    public function bind(): void;

    /**
     * @return SocketConnectionInterface|bool
     */
    public function acceptConnection();

    /**
     * @return void
     */
    public function close(): void;
}
