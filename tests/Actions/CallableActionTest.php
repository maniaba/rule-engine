<?php

declare(strict_types=1);

namespace Tests\Actions;

use Error;
use Maniaba\RuleEngine\Actions\CallableAction;
use Maniaba\RuleEngine\Context\ArrayContext;
use Maniaba\RuleEngine\Context\ContextInterface;
use RuntimeException;
use Tests\Support\TestCase;

/**
 * Testiranje CallableAction klase.
 *
 * @internal
 */
final class CallableActionTest extends TestCase
{
    public function testExecutesCallableWithContext(): void
    {
        $context  = self::createContext([]);
        $var      = 'foo';
        $callable = static function (ContextInterface $ctx) use (&$var): bool {
            $var = 'result';

            return false;
        };

        $action = new CallableAction($callable);
        $result = $action->execute($context);

        $this->assertFalse($result, 'Expected result to be false');
        $this->assertSame('result', $var, 'Expected var to be "result"');
    }

    public function testExecutesCallableWithDifferentContext(): void
    {
        $context = self::createContext([
            'foo' => 'bar',
        ]);
        $testValue = null;

        $action = new CallableAction(static function (ContextInterface $ctx) use (&$testValue): bool {
            $testValue = $ctx->getField('foo');

            return true;
        });
        $result = $action->execute($context);

        $this->assertTrue($result, 'Expected result to be true');
        $this->assertSame('bar', $testValue, 'Expected testValue to be "bar"');
    }

    public function testHandlesCallableReturningNull(): void
    {
        $context  = self::createContext([]);
        $callable = static fn (ContextInterface $ctx): bool => true;

        $action = new CallableAction($callable);
        $result = $action->execute($context);

        $this->assertTrue($result);
    }

    public function testHandlesCallableThrowingException(): void
    {
        $this->expectException(RuntimeException::class);

        $context  = self::createContext([]);
        $callable = static fn (ContextInterface $ctx) => throw new RuntimeException(Error::class);

        $action = new CallableAction($callable);
        $action->execute($context);
    }

    private static function createContext(array $args): ContextInterface
    {
        return new ArrayContext($args);
    }
}
