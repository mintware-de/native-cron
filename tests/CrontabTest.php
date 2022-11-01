<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests;

use MintwareDe\NativeCron\Content\BlankLine;
use MintwareDe\NativeCron\Content\CommentLine;
use MintwareDe\NativeCron\Content\CronJobLine;
use MintwareDe\NativeCron\Content\CrontabLineInterface;
use MintwareDe\NativeCron\Crontab;
use PHPUnit\Framework\TestCase;

class CrontabTest extends TestCase
{
    public function testConstructor(): void
    {
        $crontab = new Crontab();
        self::assertEmpty($crontab->getLines());
        self::assertTrue($crontab->isSystemCrontab());
    }

    public function testConstructorUserSpecific(): void
    {
        $crontab = new Crontab(false);
        self::assertEmpty($crontab->getLines());
        self::assertFalse($crontab->isSystemCrontab());
        $crontab->setIsSystemCrontab(true);
        self::assertTrue($crontab->isSystemCrontab());
    }

    public function testSetIsSystemCrontab(): void
    {
        $crontab = new Crontab();
        $line = new CronJobLine('* * * * * root test', true);
        $crontab->add($line);
        self::assertTrue($crontab->isSystemCrontab());
        $crontab->setIsSystemCrontab(false);
        self::assertFalse($line->getIncludeUser());
    }

    public function testParseAndBuild(): void
    {
        $content = <<<TEXT
# Edit this file to introduce tasks to be run by cron.

*/2 * * * * test argument
TEXT;

        $crontab = new Crontab(false);
        $crontab->parse($content);
        $lines = $crontab->getLines();
        self::assertCount(3, $lines);
        self::assertInstanceOf(CommentLine::class, $lines[0]);
        self::assertInstanceOf(BlankLine::class, $lines[1]);
        self::assertInstanceOf(CronJobLine::class, $lines[2]);

        self::assertEquals($content, $crontab->build());
    }

    public function testParseAndBuildWithLeading(): void
    {
        $content = <<<TEXT
 # Edit this file to introduce tasks to be run by cron.
 
 */2 * * * * test argument
TEXT;

        $crontab = new Crontab(false);
        $crontab->parse($content);
        $lines = $crontab->getLines();
        self::assertCount(3, $lines);
        self::assertInstanceOf(CommentLine::class, $lines[0]);
        self::assertInstanceOf(BlankLine::class, $lines[1]);
        self::assertInstanceOf(CronJobLine::class, $lines[2]);

        $expected = <<<TEXT
# Edit this file to introduce tasks to be run by cron.

*/2 * * * * test argument
TEXT;
        self::assertEquals($expected, $crontab->build());
    }

    public function testParseAndBuildSystemCrontab(): void
    {
        $content = <<<TEXT
# Edit this file to introduce tasks to be run by cron.

*/2 * * * * root test argument
TEXT;

        $crontab = new Crontab();
        $crontab->parse($content);
        $lines = $crontab->getLines();
        self::assertCount(3, $lines);
        self::assertInstanceOf(CommentLine::class, $lines[0]);
        self::assertInstanceOf(BlankLine::class, $lines[1]);
        self::assertInstanceOf(CronJobLine::class, $lines[2]);

        if ($lines[2] instanceof CronJobLine) {
            self::assertEquals('root', $lines[2]->getUser());
        }
        self::assertEquals($content, $crontab->build());
    }

    public function testAddLine(): void
    {
        $content = <<<TEXT
# Edit this file to introduce tasks to be run by cron.

*/2 * * * * test argument
TEXT;

        $crontab = new Crontab(false);
        $crontab
            ->add(new CommentLine(' Edit this file to introduce tasks to be run by cron.'))
            ->add(new BlankLine())
            ->add(new CronJobLine('*/2 * * * * test argument'));

        self::assertEquals($content, $crontab->build());
    }

    public function testRemove(): void
    {
        $content = <<<TEXT
# Edit this file to introduce tasks to be run by cron.
*/2 * * * * test argument
TEXT;

        $crontab = new Crontab(false);
        $blankLine = new BlankLine();
        $crontab
            ->add(new CommentLine(' Edit this file to introduce tasks to be run by cron.'))
            ->add($blankLine)
            ->add(new CronJobLine('*/2 * * * * test argument'))
            ->remove($blankLine);

        self::assertEquals($content, $crontab->build());
    }

    public function testRemoveWhere(): void
    {
        $content = <<<TEXT
# Edit this file to introduce tasks to be run by cron.
*/2 * * * * test argument
TEXT;

        $crontab = new Crontab(false);
        $crontab
            ->add(new CommentLine(' Edit this file to introduce tasks to be run by cron.'))
            ->add(new BlankLine())
            ->add(new CronJobLine('*/2 * * * * test argument'))
            ->removeWhere(
                fn (CrontabLineInterface $line) => $line instanceof CommentLine && str_contains(
                    $line->getComment(),
                    'foo'
                )
            )
            ->removeWhere(fn (CrontabLineInterface $line) => $line instanceof BlankLine);

        self::assertEquals($content, $crontab->build());
    }
}
