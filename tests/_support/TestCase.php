<?php

declare(strict_types=1);

namespace Tests\Support;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base TestCase for all tests in the project.
 */
abstract class TestCase extends PHPUnitTestCase
{
    use ReflectionHelper;
}
