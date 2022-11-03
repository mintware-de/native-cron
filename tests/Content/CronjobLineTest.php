<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests\Content;

use MintwareDe\NativeCron\Content\CronJobLine;
use MintwareDe\NativeCron\Content\CrontabLineInterface;
use PHPUnit\Framework\TestCase;

class CronjobLineTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $cronjobLine = new CronJobLine();
        $this->checkEmptyValues($cronjobLine);
        self::assertEquals('* * * * * ', $cronjobLine->build());
    }

    public function testConstructor(): void
    {
        $cronjobLine = new CronJobLine('0 0 * * * test');
        self::assertEquals('test', $cronjobLine->getCommand());
        self::assertEquals('0 0 * * * test', $cronjobLine->build());
    }

    public function testInheritance(): void
    {
        $cronjobLine = new CronJobLine();
        self::assertInstanceOf(CrontabLineInterface::class, $cronjobLine);
    }

    public function getSetUser(): void
    {
        $cronjobLine = new CronJobLine();
        self::assertNull($cronjobLine->getUser());
        $cronjobLine->setUser('maxmuster');
        self::assertEquals('maxmuster', $cronjobLine->getUser());
    }

    public function getSetIncludeUser(): void
    {
        $cronjobLine = new CronJobLine();
        self::assertFalse($cronjobLine->getIncludeUser());
        $cronjobLine->setIncludeUser(true);
        self::assertTrue($cronjobLine->getIncludeUser());
    }

    public function testParseSimple(): void
    {
        $cronjobLine = new CronJobLine();
        $line = '* * * * * test';
        $cronjobLine->parse($line);

        self::assertEquals('test', $cronjobLine->getCommand());

        $this->checkEmptyValues($cronjobLine);
    }

    public function testParseAdvanced(): void
    {
        $cronjobLine = new CronJobLine();
        $line = '0 */12 1,3-5,*/5 4-8 * test arg';
        $cronjobLine->parse($line);

        self::assertEquals('test arg', $cronjobLine->getCommand());

        $dateTimeDefinition = $cronjobLine->getDateTimeDefinition();
        self::assertCount(1, $dateTimeDefinition->getMinutes());
        $minute = $dateTimeDefinition->getMinutes()[0];
        self::assertTrue($minute->hasValue());
        self::assertFalse($minute->isRange());
        self::assertEquals(0, $minute->getValueFrom());
        self::assertEquals(1, $minute->getStep());

        self::assertCount(1, $dateTimeDefinition->getHours());
        $hours = $dateTimeDefinition->getHours()[0];
        self::assertFalse($hours->hasValue());
        self::assertEquals(12, $hours->getStep());

        self::assertCount(3, $dateTimeDefinition->getDays());
        $firstDay = $dateTimeDefinition->getDays()[0];
        self::assertTrue($firstDay->hasValue());
        self::assertFalse($firstDay->isRange());
        self::assertEquals(1, $firstDay->getValueFrom());
        self::assertEquals(1, $firstDay->getStep());

        $secondDay = $dateTimeDefinition->getDays()[1];
        self::assertTrue($secondDay->hasValue());
        self::assertTrue($secondDay->isRange());
        self::assertEquals(3, $secondDay->getValueFrom());
        self::assertEquals(5, $secondDay->getValueTo());
        self::assertEquals(1, $secondDay->getStep());

        $thirdDay = $dateTimeDefinition->getDays()[2];
        self::assertFalse($thirdDay->hasValue());
        self::assertTrue($thirdDay->isRange());
        self::assertEquals(5, $thirdDay->getStep());

        self::assertCount(1, $dateTimeDefinition->getMonths());
        $month = $dateTimeDefinition->getMonths()[0];
        self::assertTrue($month->hasValue());
        self::assertTrue($month->isRange());
        self::assertEquals(4, $month->getValueFrom());
        self::assertEquals(8, $month->getValueTo());
    }

    public function testBuildWithoutUserShouldThrow(): void
    {
        $cronjobLine = new CronJobLine('* * * * * root test', true);
        $cronjobLine->setUser(null);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('The cron job line requires a user.');

        $cronjobLine->build();
    }

    public function testBuildSimple(): void
    {
        $cronjobLine = new CronJobLine();
        $line = '* * * * * test';
        $cronjobLine->parse($line);
        self::assertEquals($line, $cronjobLine->build());
    }

    public function testBuildAdvanced(): void
    {
        $cronjobLine = new CronJobLine();
        $line = '0 */12 1,3-5,*/5 4-8 * test arg';
        $cronjobLine->parse($line);
        self::assertEquals($line, $cronjobLine->build());
    }

    public function testUserWithDashes(): void
    {
        $line = '* * * * * www-data command';
        $cronjobLine = new CronJobLine($line, true);
        self::assertEquals('www-data', $cronjobLine->getUser());
    }

    private function checkEmptyValues(CronJobLine $cronjobLine): void
    {
        $dateTimeDefinition = $cronjobLine->getDateTimeDefinition();
        self::assertCount(1, $dateTimeDefinition->getMinutes());
        $minute = $dateTimeDefinition->getMinutes()[0];
        self::assertFalse($minute->hasValue());
        self::assertEquals(0, $minute->getMin());
        self::assertEquals(59, $minute->getMax());

        self::assertCount(1, $dateTimeDefinition->getHours());
        $hours = $dateTimeDefinition->getHours()[0];
        self::assertFalse($hours->hasValue());
        self::assertEquals(0, $hours->getMin());
        self::assertEquals(23, $hours->getMax());

        self::assertCount(1, $dateTimeDefinition->getDays());
        $days = $dateTimeDefinition->getDays()[0];
        self::assertFalse($days->hasValue());
        self::assertEquals(1, $days->getMin());
        self::assertEquals(31, $days->getMax());

        self::assertCount(1, $dateTimeDefinition->getMonths());
        $month = $dateTimeDefinition->getMonths()[0];
        self::assertFalse($month->hasValue());
        self::assertEquals(1, $month->getMin());
        self::assertEquals(12, $month->getMax());

        self::assertCount(1, $dateTimeDefinition->getWeekdays());
        $weekdays = $dateTimeDefinition->getWeekdays()[0];
        self::assertFalse($weekdays->hasValue());
        self::assertEquals(0, $weekdays->getMin());
        self::assertEquals(6, $weekdays->getMax());
    }
}
