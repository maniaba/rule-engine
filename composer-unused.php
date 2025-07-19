<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;
use ComposerUnused\ComposerUnused\Configuration\PatternFilter;
use Webmozart\Glob\Glob;

return static fn (Configuration $config): Configuration => $config;
    // ->addNamedFilter(NamedFilter::fromString('symfony/config'))
    // ->addPatternFilter(PatternFilter::fromString('/symfony-.*/'));
