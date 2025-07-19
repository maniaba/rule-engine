<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Enums;

enum CollectionConditionType: string
{
    case OR  = 'or';
    case AND = 'and';
}


