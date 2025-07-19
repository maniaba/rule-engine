<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Traits;

trait FailureMessagesTrait
{
    private array|string|null $failureMessage = null;

    public function getFailureMessage(): array|string|null
    {
        return $this->failureMessage;
    }

    protected function setFailureMessage(array|string|null $message): void
    {
        $this->failureMessage = $message;
    }
}
