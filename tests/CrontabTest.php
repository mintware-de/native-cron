<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests;

use MintwareDe\NativeCron\BlankLine;
use MintwareDe\NativeCron\CommentLine;
use MintwareDe\NativeCron\CronjobLine;
use MintwareDe\NativeCron\Crontab;
use PHPUnit\Framework\TestCase;

class CrontabTest extends TestCase
{
    public function testConstructor(): void
    {
        $crontab = new Crontab();
        self::assertEmpty($crontab->getLines());
    }

    public function testParseAndBuild(): void
    {
        $content = <<<TEXT
# Edit this file to introduce tasks to be run by cron.

*/2 * * * * test argument
TEXT;

        $crontab = new Crontab();
        $crontab->parse($content);
        $lines = $crontab->getLines();
        self::assertCount(3, $lines);
        self::assertInstanceOf(CommentLine::class, $lines[0]);
        self::assertInstanceOf(BlankLine::class, $lines[1]);
        self::assertInstanceOf(CronjobLine::class, $lines[2]);

        self::assertEquals($content, $crontab->build());
    }
}
