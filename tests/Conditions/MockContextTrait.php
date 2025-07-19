<?php

declare(strict_types=1);

namespace Tests\Conditions;

use Maniaba\RuleEngine\Context\ContextInterface;

/**
 * @internal This class is for internal use only
 */
trait MockContextTrait
{
    private function createMockContext(array $fields): ContextInterface
    {
        $mockContext = $this->createMock(ContextInterface::class);

        $mockContext->method('hasField')
            ->willReturnCallback(static fn(string $field): bool => \array_key_exists($field, $fields))
        ;

        $mockContext->method('getField')
            ->willReturnCallback(static fn(string $field) => $fields[$field] ?? null)
        ;

        return $mockContext;
    }
}
