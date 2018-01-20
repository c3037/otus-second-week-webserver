<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service;

interface SocketInterface
{
    /**
     * @return void
     */
    public function bind(): void;

    /**
     * @return resource
     */
    public function getConnection();
}
