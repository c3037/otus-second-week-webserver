<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Dto\BindParams;

interface BindParamsDeterminatorInterface
{
    /**
     * @return BindParams
     */
    public function determine(): BindParams;
}
