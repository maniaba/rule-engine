<?php

declare(strict_types=1);

namespace Maniaba\RuleEngine\Builders;

use JsonException;
use Maniaba\RuleEngine\Rules\RuleSet;
use SplFileInfo;

final class JsonBuilder extends ArrayBuilder
{
    /**
     * @throws JsonException
     */
    public function parseFile(SplFileInfo $file): RuleSet
    {
        $config = file_get_contents($file->getRealPath());

        return $this->build($config);
    }

    /**
     * @throws JsonException
     */
    public function build(mixed $config): RuleSet
    {
        if (! \is_string($config) || json_validate($config) === false) {
            throw new JsonException('Configuration must be a JSON.');
        }

        $config = json_decode($config, true, 512, JSON_THROW_ON_ERROR);

        return parent::build($config);
    }
}
