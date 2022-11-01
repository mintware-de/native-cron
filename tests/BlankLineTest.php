<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests;

use MintwareDe\NativeCron\BlankLine;
use MintwareDe\NativeCron\CrontabLineInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class BlankLineTest extends TestCase
{
    public function testInheritance(): void
    {
        $blankLine = new BlankLine();
        self::assertInstanceOf(CrontabLineInterface::class, $blankLine);
    }

    public function testParseFailsNotEmpty(): void
    {
        $blankLine = new BlankLine();

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Parsing non-empty lines is not supported.');
        $blankLine->parse(' ');
    }

    public function testParsePass(): void
    {
        $blankLine = new BlankLine();
        $blankLine->parse('');
        self::assertTrue(true);
    }

    public function testBuild(): void
    {
        $blankLine = new BlankLine();
        self::assertEquals('', $blankLine->build());
    }
}
