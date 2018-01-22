<?php
declare(strict_types=1);

namespace c3037\Otus\SecondWeek\BracketsServer\Worker\Service\RequestProcessor;

use c3037\Otus\FirstWeek\Library\ValidatorInterface;
use InvalidArgumentException;

final class BracketsValidationProcessor implements RequestProcessorInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $request): string
    {
        $resultMessagesMap = [
            true => 'String is valid',
            false => 'String is NOT valid',
        ];

        try {
            $response = $resultMessagesMap[$this->validator->validate($request)];
        } catch (InvalidArgumentException $e) {
            $response = sprintf('Validation error: %s', $e->getMessage());
        }

        return sprintf('%s%s', $response, PHP_EOL);
    }
}
