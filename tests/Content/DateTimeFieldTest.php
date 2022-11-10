<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests\Content;

use MintwareDe\NativeCron\Content\DateTimeField;
use PHPUnit\Framework\TestCase;

class DateTimeFieldTest extends TestCase
{
    public function testConstructor(): void
    {
        $evenMinutesField = new DateTimeField(0, 59);
        self::assertEquals(0, $evenMinutesField->getMin());
        self::assertEquals(59, $evenMinutesField->getMax());

        self::assertEquals(0, $evenMinutesField->getValueFrom());
        self::assertEquals(59, $evenMinutesField->getValueTo());
    }

    public function testSetValueFailsOutOfRangeMin(): void
    {
        $monthField = new DateTimeField(1, 12);
        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage('-1 is not in the range 1 to 12.');
        $monthField->setValue(-1);
    }

    public function testSetValueFailsOutOfRangeMax(): void
    {
        $monthField = new DateTimeField(1, 12);
        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage('13 is not in the range 1 to 12.');
        $monthField->setValue(13);
    }

    public function testSetRangeValueFailsOutOfRangeMin(): void
    {
        $monthField = new DateTimeField(1, 12);
        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage('-1 is not in the range 1 to 12.');
        $monthField->setRangeValue(-1, 12);
    }

    public function testSetRangeValueFailsOutOfRangeMax(): void
    {
        $monthField = new DateTimeField(1, 12);
        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage('13 is not in the range 1 to 12.');
        $monthField->setRangeValue(1, 13);
    }

    public function testSetValue(): void
    {
        $monthField = new DateTimeField(1, 12);
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(12, $monthField->getValueTo());
        self::assertTrue($monthField->isRange());

        $monthField->setValue(1);
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(1, $monthField->getValueTo());
        self::assertFalse($monthField->isRange());

        $monthField->setRangeValue(1, 3);
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(3, $monthField->getValueTo());
        self::assertTrue($monthField->isRange());

        $monthField->unsetValue();
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(12, $monthField->getValueTo());
        self::assertTrue($monthField->isRange());
    }

    public function testHasValue(): void
    {
        $monthField = new DateTimeField(1, 12);
        self::assertFalse($monthField->hasValue());
        $monthField->setRangeValue(1, 3);
        self::assertTrue($monthField->hasValue());
        $monthField->setValue(1);
        self::assertTrue($monthField->hasValue());
        $monthField->unsetValue();
        self::assertFalse($monthField->hasValue());
    }

    public function testSetStepFails(): void
    {
        $hoursField = new DateTimeField(0, 23);
        self::expectException(\OutOfBoundsException::class);
        self::expectExceptionMessage('The step must be greater than 0.');
        $hoursField->setStep(0);
    }

    public function testSetStepPass(): void
    {
        $hoursField = new DateTimeField(0, 23);
        self::assertEquals(1, $hoursField->getStep());
        $hoursField->setStep(2);
        self::assertEquals(2, $hoursField->getStep());
    }

    public function testBuildWithoutStep(): void
    {
        $monthField = new DateTimeField(1, 12);

        // No value
        self::assertEquals('*', $monthField->build());

        // Single value
        $monthField->setValue(2);
        self::assertEquals('2', $monthField->build());

        // Range value
        $monthField->setRangeValue(1, 3);
        self::assertEquals('1-3', $monthField->build());
    }

    public function testBuildWithStep(): void
    {
        $monthField = new DateTimeField(1, 12);
        $monthField->setStep(2);

        // No value
        self::assertEquals('*/2', $monthField->build());

        // Single value
        $monthField->setValue(2);
        self::assertEquals('2/2', $monthField->build());

        // Range value
        $monthField->setRangeValue(1, 3);
        self::assertEquals('1-3/2', $monthField->build());
    }

    public function testParseWithoutStep(): void
    {
        $monthField = new DateTimeField(1, 12);
        self::assertFalse($monthField->hasValue());

        $monthField->parse('11');
        self::assertEquals(11, $monthField->getValueFrom());
        self::assertEquals(11, $monthField->getValueTo());

        $monthField->parse('*');
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(12, $monthField->getValueTo());

        $monthField->parse('1-3');
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(3, $monthField->getValueTo());
    }

    public function testParseWithStep(): void
    {
        $monthField = new DateTimeField(1, 12);
        self::assertFalse($monthField->hasValue());

        $monthField->parse('3/2');
        self::assertEquals(3, $monthField->getValueFrom());
        self::assertEquals(3, $monthField->getValueTo());
        self::assertEquals(2, $monthField->getStep());

        $monthField->parse('*/2');
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(12, $monthField->getValueTo());
        self::assertEquals(2, $monthField->getStep());
    }

    public function testParseWithAbbreviationsValues(): void
    {
        $monthField = new DateTimeField(1, 12, [
            'jan' => 1,
            'feb' => '2',
            'mar' => '3',
            'apr' => 4,
        ]);
        self::assertFalse($monthField->hasValue());

        $monthField->parse('jan-apr/2');
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(4, $monthField->getValueTo());
        self::assertEquals(2, $monthField->getStep());

        $monthField->parse('jan');
        self::assertEquals(1, $monthField->getValueFrom());
        self::assertEquals(1, $monthField->getValueTo());
        self::assertEquals(1, $monthField->getStep());
    }
}
