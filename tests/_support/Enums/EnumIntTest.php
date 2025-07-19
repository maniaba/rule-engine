<?php

declare(strict_types=1);

namespace Tests\Support\Enums;

enum EnumIntTest: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;
}
