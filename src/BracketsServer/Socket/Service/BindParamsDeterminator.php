<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Socket\Service;

use c3037\Otus\SecondWeek\BracketsServer\Socket\Dto\BindParams;

final class BindParamsDeterminator implements BindParamsDeterminatorInterface
{
    /**
     * @var array
     */
    private $scriptArguments;

    /**
     * @var array
     */
    private $hostConfig;

    /**
     * @var array
     */
    private $portConfig;

    /**
     * @param array $scriptArguments
     * @param array $hostConfig
     * @param array $portConfig
     */
    public function __construct(array $scriptArguments, array $hostConfig, array $portConfig)
    {
        $this->scriptArguments = $scriptArguments;
        $this->hostConfig = $hostConfig;
        $this->portConfig = $portConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function determine(): BindParams
    {
        return new BindParams($this->obtainHost(), $this->obtainPort());
    }

    /**
     * @return string
     */
    private function obtainHost(): string
    {
        return (string)$this->obtain($this->hostConfig);
    }

    /**
     * @return int
     */
    private function obtainPort(): int
    {
        return (int)$this->obtain($this->portConfig);
    }

    /**
     * @param array $config
     * @return mixed
     */
    private function obtain(array $config)
    {
        return
            $this->getcli($config['cli_arguments'])
                ?: getenv($config['environment_variable'])
                ?: $config['default'];
    }

    /**
     * @param array $config
     * @return string
     */
    private function getcli(array $config): string
    {
        $pattern = sprintf('#(?:-%s|--%s)=(.*)#i', $config['short'], $config['long']);

        foreach ($this->scriptArguments as $argument) {
            if (preg_match($pattern, $argument, $match)) {
                return $match[1];
            }
        }

        return '';
    }
}
