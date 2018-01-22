<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Worker\Service\RequestProcessor;

interface RequestProcessorInterface
{
    /**
     * @param string $request
     * @return string
     */
    public function process(string $request): string;
}
