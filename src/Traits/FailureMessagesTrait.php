<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Traits;

trait FailureMessagesTrait
{
    private null|array|string $failureMessage = null;

    public function getFailureMessage(): null|array|string
    {
        return $this->failureMessage;
    }

    protected function setFailureMessage(null|array|string $message): void
    {
        $this->failureMessage = $message;
    }
}
