<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests\FileSystem;

use MintwareDe\NativeCron\Filesystem\CrontabFileLocatorInterface;
use MintwareDe\NativeCron\Filesystem\DarwinCrontabFileLocator;
use PHPUnit\Framework\TestCase;

class DarwinCrontabFileLocatorTest extends TestCase
{
    private DarwinCrontabFileLocator $locator;

    protected function setUp(): void
    {
        $this->locator = new DarwinCrontabFileLocator();
    }

    public function testInheritance(): void
    {
        self::assertInstanceOf(CrontabFileLocatorInterface::class, $this->locator);
    }

    public function testLocateDropInCrontab(): void
    {
        self::expectException(\RuntimeException::class);
        $this->locator->locateDropInCrontab('app');
    }

    public function testLocateUserCrontab(): void
    {
        $expected = '/usr/lib/cron/tabs/root';
        self::assertEquals($expected, $this->locator->locateUserCrontab('root'));
    }

    public function testLocateSystemCrontab(): void
    {
        $expected = '/etc/crontab';
        self::assertEquals($expected, $this->locator->locateSystemCrontab());
    }
}
