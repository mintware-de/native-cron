<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests;

use MintwareDe\NativeCron\CommentLine;
use MintwareDe\NativeCron\CrontabLineInterface;
use PHPUnit\Framework\TestCase;

class CommentLineTest extends TestCase
{
    public function testInheritance(): void
    {
        $commentLine = new CommentLine();
        self::assertInstanceOf(CrontabLineInterface::class, $commentLine);
    }

    public function testParseFailsInvalidContent(): void
    {
        $commentLine = new CommentLine();
        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('A comment line must start with a # character.');
        $commentLine->parse('test');
    }

    public function testGetSetComment(): void
    {
        $commentLine = new CommentLine();
        self::assertEquals('', $commentLine->getComment());
        $commentLine->setComment('test');
        self::assertEquals('test', $commentLine->getComment());
    }

    public function testParsePass(): void
    {
        $commentLine = new CommentLine();
        $commentLine->parse('# test');
        self::assertEquals(' test', $commentLine->getComment());
    }

    public function testBuild(): void
    {
        $commentLine = new CommentLine();
        self::assertEquals('#', $commentLine->build());

        $commentLine->setComment('test');
        self::assertEquals('#test', $commentLine->build());
    }
}
