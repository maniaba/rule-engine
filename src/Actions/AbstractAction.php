<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Actions;

use Maniaba\RuleEngine\Traits\FailureMessagesTrait;

abstract class AbstractAction implements ActionInterface
{
    use FailureMessagesTrait;
}
