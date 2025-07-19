<?php

declare(strict_types=1);

use Nexus\CsConfig\Factory;
use Nexus\CsConfig\Ruleset\Nexus81;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->files()
    ->in([
        __DIR__.'/src/',
        __DIR__.'/tests/',
    ])
    ->exclude([
        'build',
        'Views',
    ])
    ->append([
        __FILE__,
        __DIR__.'/rector.php',
    ])
;

$overrides = [
    'declare_strict_types' => true,
    // 'void_return'          => true,
    'php_unit_attributes' => true,
];

$options = [
    'finder' => $finder,
    'cacheFile' => 'build/.php-cs-fixer.cache',
];

return Factory::create(new Nexus81(), $overrides, $options)->forProjects();
