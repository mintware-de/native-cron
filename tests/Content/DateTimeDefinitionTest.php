<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests\Content;

use MintwareDe\NativeCron\Content\DateTimeDefinition;
use PHPUnit\Framework\TestCase;

class DateTimeDefinitionTest extends TestCase
{
    public function testExists(): void
    {
        $definition = new DateTimeDefinition();

        self::assertCount(1, $definition->getMinutes());
        $minute = $definition->getMinutes()[0];
        self::assertFalse($minute->hasValue());
        self::assertEquals(0, $minute->getMin());
        self::assertEquals(59, $minute->getMax());

        self::assertCount(1, $definition->getHours());
        $hours = $definition->getHours()[0];
        self::assertFalse($hours->hasValue());
        self::assertEquals(0, $hours->getMin());
        self::assertEquals(23, $hours->getMax());

        self::assertCount(1, $definition->getDays());
        $days = $definition->getDays()[0];
        self::assertFalse($days->hasValue());
        self::assertEquals(1, $days->getMin());
        self::assertEquals(31, $days->getMax());

        self::assertCount(1, $definition->getMonths());
        $month = $definition->getMonths()[0];
        self::assertFalse($month->hasValue());
        self::assertEquals(1, $month->getMin());
        self::assertEquals(12, $month->getMax());

        self::assertCount(1, $definition->getWeekdays());
        $weekdays = $definition->getWeekdays()[0];
        self::assertFalse($weekdays->hasValue());
        self::assertEquals(0, $weekdays->getMin());
        self::assertEquals(6, $weekdays->getMax());
    }

    public function testSetters(): void
    {
        $definition = new DateTimeDefinition();
        $definition
            ->setMinutes('1,2')
            ->setHours('3,4')
            ->setDays('5,6')
            ->setMonths('7-8')
            ->setWeekdays('1,3,5,0-5/2,6')
        ;
        self::assertEquals('1,2 3,4 5,6 7-8 1,3,5,0-5/2,6', $definition->build());

    }

    public function testParseFailsInvalidFormat(): void
    {
        $definition = new DateTimeDefinition();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('The date time definition string does not match the expected format.');

        $definition->parse("test");
    }

    public function testParse(): void
    {
        $definition = new DateTimeDefinition();
        $definition->parse("0 */12 1,3-5,*/5 4-8 *");

        self::assertCount(1, $definition->getMinutes());
        $minute = $definition->getMinutes()[0];
        self::assertTrue($minute->hasValue());
        self::assertFalse($minute->isRange());
        self::assertEquals(0, $minute->getValueFrom());
        self::assertEquals(1, $minute->getStep());

        self::assertCount(1, $definition->getHours());
        $hours = $definition->getHours()[0];
        self::assertFalse($hours->hasValue());
        self::assertEquals(12, $hours->getStep());

        self::assertCount(3, $definition->getDays());
        $firstDay = $definition->getDays()[0];
        self::assertTrue($firstDay->hasValue());
        self::assertFalse($firstDay->isRange());
        self::assertEquals(1, $firstDay->getValueFrom());
        self::assertEquals(1, $firstDay->getStep());

        $secondDay = $definition->getDays()[1];
        self::assertTrue($secondDay->hasValue());
        self::assertTrue($secondDay->isRange());
        self::assertEquals(3, $secondDay->getValueFrom());
        self::assertEquals(5, $secondDay->getValueTo());
        self::assertEquals(1, $secondDay->getStep());

        $thirdDay = $definition->getDays()[2];
        self::assertFalse($thirdDay->hasValue());
        self::assertTrue($thirdDay->isRange());
        self::assertEquals(5, $thirdDay->getStep());

        self::assertCount(1, $definition->getMonths());
        $month = $definition->getMonths()[0];
        self::assertTrue($month->hasValue());
        self::assertTrue($month->isRange());
        self::assertEquals(4, $month->getValueFrom());
        self::assertEquals(8, $month->getValueTo());
    }

    public function testParseBuild(): void
    {
        $tests = [
            '* * * * *',
            '0 0 1 1 0',
            '1-2 1-12/2 * * *',
        ];

        foreach ($tests as $test) {
            $definition = new DateTimeDefinition();
            $definition->parse($test);
            self::assertEquals($test, $definition->build());
        }
    }
}
