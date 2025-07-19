<?php

declare(strict_types=1);

namespace Tests\Context;

use InvalidArgumentException;
use Maniaba\RuleEngine\Context\FieldSelector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\Enums\EnumIntTest;
use Tests\Support\Enums\SimpleEnum;
use Tests\Support\TestCase;

/**
 * @internal
 */
#[Group('Others')]
final class FieldSelectorTest extends TestCase
{
    /**
     * Test data.
     *
     * The data structure is similar to the one from the examples in the explanation, but expanded
     * with additional fields to test more cases.
     */
    private array $data;

    private stdClass $dataObject;

    protected function setUp(): void
    {
        $this->data = [
            'users' => [
                ['id' => 1, 'name' => 'Alice', 'age' => 25, 'active' => true],
                ['id' => 2, 'name' => 'Bob', 'age' => 30, 'active' => false],
                ['id' => 3, 'name' => 'Charlie', 'age' => 35, 'active' => true],
            ],
            'logs' => [
                ['id' => 1, 'level' => 'info', 'message' => 'Initialization successful.'],
                ['id' => 2, 'level' => 'error', 'message' => 'Database connection failed.'],
                ['id' => 3, 'level' => 'info', 'message' => 'Data processing completed.'],
            ],
            'departments' => [
                [
                    'name'  => 'Development',
                    'teams' => [
                        [
                            'teamName' => 'Backend',
                            'members'  => [
                                ['id' => 101, 'name' => 'Eve', 'role' => 'Engineer'],
                                ['id' => 102, 'name' => 'Frank', 'role' => 'Engineer'],
                                ['id' => 103, 'name' => 'Grace', 'role' => 'Lead'],
                            ],
                        ],
                        [
                            'teamName' => 'Frontend',
                            'members'  => [
                                ['id' => 201, 'name' => 'Heidi', 'role' => 'Engineer'],
                                ['id' => 202, 'name' => 'Ivan', 'role' => 'Engineer'],
                            ],
                        ],
                    ],
                ],
                [
                    'name'  => 'Operations',
                    'teams' => [
                        [
                            'teamName' => 'Infrastructure',
                            'members'  => [
                                ['id' => 301, 'name' => 'Judy', 'role' => 'Ops'],
                                ['id' => 302, 'name' => 'Ken', 'role' => 'Ops'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // convert data deep nested array to object
        $this->dataObject = json_decode(json_encode($this->data));
    }

    public function testEvaluateConditionInvalidOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->callEvaluateCondition(5, '<>', 3);
    }

    public function testEvaluateConditionWithBackedEnum(): void
    {
        // Testing BackedEnum values
        $result = $this->callEvaluateCondition(EnumIntTest::ACTIVE, '=', 1);
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition(EnumIntTest::ACTIVE, '!=', 5);
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition(EnumIntTest::INACTIVE, '=', 0);
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition(EnumIntTest::INACTIVE, '!=', 5);
        $this->assertTrue($result);
    }

    public function testEvaluateConditionWithUnitEnum(): void
    {
        // Testing UnitEnum values (uses `name`)
        $result = $this->callEvaluateCondition(SimpleEnum::OPTION_ONE, '=', 'OPTION_ONE');
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition(SimpleEnum::OPTION_ONE, '!=', 'OPTION_TWO');
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition(SimpleEnum::OPTION_TWO, '=', 'OPTION_TWO');
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition(SimpleEnum::OPTION_TWO, '!=', 'OPTION_ONE');
        $this->assertTrue($result);
    }

    public function testEvaluateConditionWithStandardValues(): void
    {
        // Testing standard values (numbers, strings, etc.)
        $result = $this->callEvaluateCondition(5, '>', 3);
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition(3, '<', 5);
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition('apple', '=', 'apple');
        $this->assertTrue($result);

        $result = $this->callEvaluateCondition('apple', '!=', 'orange');
        $this->assertTrue($result);
    }

    /**
     * Testira osnovni pristup indeksima u nizu.
     */
    #[DataProvider('provideBasicIndexAccess')]
    public function testBasicIndexAccess(string $selector, array $expected): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $this->assertSame($expected, $fieldSelector->getField($selector));
    }

    /**
     * Data provider za testiranje osnovnog pristupa poljima preko numeričkog indeksa.
     *
     * @return list<array>
     */
    public static function provideBasicIndexAccess(): iterable
    {
        yield 'prvi korisnik' => ['users[0]', ['id' => 1, 'name' => 'Alice', 'age' => 25, 'active' => true]];

        yield 'drugi korisnik' => ['users[1]', ['id' => 2, 'name' => 'Bob', 'age' => 30, 'active' => false]];

        yield 'treci korisnik' => ['users[2]', ['id' => 3, 'name' => 'Charlie', 'age' => 35, 'active' => true]];

        yield 'prvi log' => ['logs[0]', ['id' => 1, 'level' => 'info', 'message' => 'Initialization successful.']];

        yield 'drugi log' => ['logs[1]', ['id' => 2, 'level' => 'error', 'message' => 'Database connection failed.']];
    }

    /**
     * Testira filtriranje jednakosti (`=`).
     */
    #[DataProvider('provideEqualityFilter')]
    public function testEqualityFilter(string $selector, array $expected): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $this->assertSame($expected, $fieldSelector->getField($selector));
    }

    /**
     * Data provider za testiranje filtriranja po ključu i vrijednosti (operator `=`).
     *
     * @return list<array>
     */
    public static function provideEqualityFilter(): iterable
    {
        yield 'korisnik s id=2' => ['users[id:2]', ['id' => 2, 'name' => 'Bob', 'age' => 30, 'active' => false]];

        yield 'korisnik s imenom Alice' => ['users[name:Alice]', ['id' => 1, 'name' => 'Alice', 'age' => 25, 'active' => true]];

        yield 'log s level=error' => ['logs[level:error]', ['id' => 2, 'level' => 'error', 'message' => 'Database connection failed.']];
    }

    /**
     * Testira numerička filtriranja (>, >=, <, <=).
     */
    #[DataProvider('provideNumericComparisons')]
    public function testNumericComparisons(string $selector, array $expected): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $this->assertSame($expected, $fieldSelector->getField($selector));
    }

    /**
     * Data provider za testiranje filtriranja po numeričkim usporedbama.
     * Testiramo `>`, `>=`, `<`, `<=`.
     *
     * @return list<array>
     */
    public static function provideNumericComparisons(): iterable
    {
        yield 'prvi korisnik s age>=30' => ['users[age:>=30]', ['id' => 2, 'name' => 'Bob', 'age' => 30, 'active' => false]];

        yield 'prvi korisnik s age>25' => ['users[age:>25]', ['id' => 2, 'name' => 'Bob', 'age' => 30, 'active' => false]];

        yield 'prvi korisnik s age<30' => ['users[age:<30]', ['id' => 1, 'name' => 'Alice', 'age' => 25, 'active' => true]];

        yield 'prvi korisnik s age<=35' => ['users[age:<=35]', ['id' => 1, 'name' => 'Alice', 'age' => 25, 'active' => true]];
    }

    /**
     * Testira filtriranje po boolean vrijednostima.
     */
    #[DataProvider('provideBooleanFilter')]
    public function testBooleanFilter(string $selector, array $expected): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $this->assertSame($expected, $fieldSelector->getField($selector));
    }

    /**
     * Data provider za testiranje filtriranja po boolean vrijednostima.
     *
     * @return list<array>
     */
    public static function provideBooleanFilter(): iterable
    {
        yield 'prvi korisnik s active=true' => ['users[active:true]', ['id' => 1, 'name' => 'Alice', 'age' => 25, 'active' => true]];

        yield 'prvi korisnik s active=false' => ['users[active:false]', ['id' => 2, 'name' => 'Bob', 'age' => 30, 'active' => false]];
    }

    /**
     * Testira dohvaćanje duboko ugniježđenih vrijednosti (chaining).
     *
     * Primjer: Dohvaćamo poruku loga s level=error.
     */
    public function testChainedSelectors(): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $message       = $fieldSelector->getField('logs[level:error]->message');
        $this->assertSame('Database connection failed.', $message);
    }

    /**
     * Testira scenarije u kojima bi trebala biti bačena iznimka (InvalidArgumentException).
     */
    #[DataProvider('provideInvalidSelectorsThrowExceptions')]
    public function testInvalidSelectorsThrowExceptions(string $selector): void
    {
        $this->expectException(InvalidArgumentException::class);
        $fieldSelector = new FieldSelector($this->data);
        $fieldSelector->getField($selector);
    }

    /**
     * Data provider za negativne slučajeve gdje očekujemo iznimku.
     *
     * @return list<array>
     */
    public static function provideInvalidSelectorsThrowExceptions(): iterable
    {
        yield 'nepostojeći indeks' => ['users[99]'];

        yield 'nepostojeći ključ' => ['users[id:9999]'];

        yield 'neispravan filter' => ['users[invalidFilter]']; // Nepoznat format

        yield 'nepoznati operator' => ['users[id:<>5]']; // Operator <> nije podržan

        yield 'nepostojeći property' => ['nonexistent[key:value]'];
    }

    public function testDeepNestedAccess(): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $name          = $fieldSelector->getField('departments[0]->teams[0]->members[1]->name');
        $this->assertSame('Frank', $name);
    }

    public function testDeepNestedFilterById(): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $member        = $fieldSelector->getField('departments[name:Development]->teams[teamName:Backend]->members[id:103]');
        $this->assertSame(['id' => 103, 'name' => 'Grace', 'role' => 'Lead'], $member);
    }

    public function testDeepNestedFilterByRole(): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $member        = $fieldSelector->getField('departments[name:Development]->teams[teamName:Frontend]->members[role:Engineer]');
        // Očekujemo prvog člana s role = Engineer, to je Heidi (id=201)
        $this->assertSame(['id' => 201, 'name' => 'Heidi', 'role' => 'Engineer'], $member);
    }

    public function testFilterOnHigherLevelThenDrillDown(): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $name          = $fieldSelector->getField('departments[name:Operations]->teams[teamName:Infrastructure]->members[id:301]->name');
        $this->assertSame('Judy', $name);
    }

    public function testDeepNestedFilterNoResult(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $fieldSelector = new FieldSelector($this->data);
        $fieldSelector->getField('departments[name:Development]->teams[teamName:Backend]->members[id:999]');
    }

    #[DataProvider('provideDeepNestedDataProvider')]
    public function testDeepNestedDataProvider(string $selector, array $expected): void
    {
        $fieldSelector = new FieldSelector($this->data);
        $this->assertSame($expected, $fieldSelector->getField($selector));
    }

    public static function provideDeepNestedDataProvider(): iterable
    {
        yield 'Backend team, member Eve' => [
            'departments[name:Development]->teams[teamName:Backend]->members[id:101]',
            ['id' => 101, 'name' => 'Eve', 'role' => 'Engineer'],
        ];

        yield 'Frontend team, member Ivan' => [
            'departments[name:Development]->teams[teamName:Frontend]->members[name:Ivan]',
            ['id' => 202, 'name' => 'Ivan', 'role' => 'Engineer'],
        ];

        yield 'Infrastructure team, member Ken by role' => [
            'departments[name:Operations]->teams[teamName:Infrastructure]->members[role:Ops]',
            ['id' => 301, 'name' => 'Judy', 'role' => 'Ops'],
            // Primijetite da će vratiti prvog člana koji zadovoljava role:Ops, to je Judy, ne Ken,
            // pa ako želimo Ken-a, mogli bismo testirati neki drugi filter ili redoslijed.
        ];
    }

    public function testGetUserByIndexInObjects(): void
    {
        $fieldSelector = new FieldSelector($this->dataObject);

        $user = $fieldSelector->getField('users[1]'); // Dohvaća drugog korisnika (Bob)
        $this->assertInstanceOf(stdClass::class, $user);
        $this->assertSame(2, $user->id);
        $this->assertSame('Bob', $user->name);
        $this->assertFalse($user->active);
    }

    public function testFilterUserByIdInObjects(): void
    {
        $fieldSelector = new FieldSelector($this->dataObject);

        $user = $fieldSelector->getField('users[id:3]'); // Dohvaća korisnika s ID 3 (Charlie)
        $this->assertInstanceOf(stdClass::class, $user);
        $this->assertSame(3, $user->id);
        $this->assertSame('Charlie', $user->name);
        $this->assertTrue($user->active);
    }

    public function testFilterUserByBooleanValueInObjects(): void
    {
        $fieldSelector = new FieldSelector($this->dataObject);

        $user = $fieldSelector->getField('users[active:true]'); // Dohvaća prvog aktivnog korisnika (Alice)
        $this->assertInstanceOf(stdClass::class, $user);
        $this->assertSame(1, $user->id);
        $this->assertSame('Alice', $user->name);
    }

    public function testFilterLogByLevelInObjects(): void
    {
        $fieldSelector = new FieldSelector($this->dataObject);

        $message = $fieldSelector->getField('logs[level:error]->message'); // Dohvaća poruku error loga
        $this->assertSame('Database connection failed.', $message);
    }

    public function testFilterNestedObject(): void
    {
        $fieldSelector = new FieldSelector($this->dataObject);

        $member = $fieldSelector->getField('departments[name:Development]->teams[teamName:Backend]->members[id:103]');
        $this->assertInstanceOf(stdClass::class, $member);
        $this->assertSame(103, $member->id);
        $this->assertSame('Grace', $member->name);
        $this->assertSame('Lead', $member->role);
    }

    public function testUnsupportedOperatorWithObjects(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $fieldSelector = new FieldSelector($this->dataObject);
        $fieldSelector->getField('users[id:<>5]'); // Nepodržan operator '<>'
    }

    public function testNonExistentPropertyInObjects(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $fieldSelector = new FieldSelector($this->dataObject);
        $fieldSelector->getField('users[nonexistentKey:xyz]'); // Nepostojeće svojstvo
    }

    public function testDeepNestedObjectChain(): void
    {
        $fieldSelector = new FieldSelector($this->dataObject);

        $teamName = $fieldSelector->getField('departments[name:Development]->teams[0]->teamName');
        $this->assertSame('Backend', $teamName);

        $memberName = $fieldSelector->getField('departments[name:Development]->teams[0]->members[0]->name');
        $this->assertSame('Eve', $memberName);
    }

    private function callEvaluateCondition(mixed $itemValue, string $operator, mixed $value): bool
    {
        $fs            = new FieldSelector([]);
        $privateMethod = $this->getPrivateMethodInvoker($fs, 'evaluateCondition');

        return $privateMethod($itemValue, $operator, $value);
    }
}
