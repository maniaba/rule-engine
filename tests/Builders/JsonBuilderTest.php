<?php

declare(strict_types=1);

namespace Tests\Builders;

use CodeIgniter\Files\File;
use JsonException;
use Maniaba\RuleEngine\Actions\CallableAction;
use Maniaba\RuleEngine\Builders\JsonBuilder;
use Maniaba\RuleEngine\Rules\RuleSet;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Actions\DummyArgumentsAction;
use Tests\Support\TestCase;

/**
 * Testiranje JsonBuilder klase.
 *
 * @internal
 */
#[Group('Others')]
final class JsonBuilderTest extends TestCase
{
    use BuilderTestDemoConfigTrait;

    public function testParseFile(): void
    {
        $builder = new JsonBuilder();
        $builder->actions()->registerAction('actionName1', DummyArgumentsAction::class);

        $file = self::createTempJsonFile();

        $ruleSet = $builder->parseFile($file);

        $this->assertInstanceOf(RuleSet::class, $ruleSet);
    }

    // wrong json

    public function testParseFileThrowsExceptionOnInvalidJson(): void
    {
        $this->expectException(JsonException::class);

        $builder = new JsonBuilder();

        $builder->build('{"node": "action"');
    }

    protected function getBuilder(): RuleSet
    {
        $builder = new JsonBuilder();

        $dummyAction = new CallableAction(static fn (): null => null);
        $builder->actions()->registerAction('actionName1', $dummyAction);
        $builder->actions()->registerAction('actionName2', $dummyAction);
        $builder->actions()->registerAction('rejectDeposit', $dummyAction);
        $builder->actions()->registerAction('actionName3', $dummyAction);
        $builder->actions()->registerAction('actionName4', $dummyAction);

        return $builder->build(json_encode(self::configBuilder()));
    }

    private static function createTempJsonFile(): File
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'json');
        helper('filesystem');
        write_file($tempFile, json_encode([
            'node'       => 'action',
            'actionName' => 'actionName1',
            'arguments'  => [
                'arguments' => 'testField',
            ],
        ]));

        return new File($tempFile);
    }
}
