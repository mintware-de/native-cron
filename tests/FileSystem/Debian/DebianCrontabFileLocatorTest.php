<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests\FileSystem\Debian;

use MintwareDe\NativeCron\Filesystem\CrontabFileLocatorInterface;
use MintwareDe\NativeCron\Filesystem\DebianCrontabFileLocator;
use PHPUnit\Framework\TestCase;

class DebianCrontabFileLocatorTest extends TestCase
{
    private DebianCrontabFileLocator $locator;

    protected function setUp(): void
    {
        $this->locator = new DebianCrontabFileLocator();
    }

    public function testExists(): void
    {
        self::assertInstanceOf(CrontabFileLocatorInterface::class, $this->locator);
    }

    public function testLocateDropInCrontab(): void
    {
        $expected = '/etc/cron.d/app';
        self::assertEquals($expected, $this->locator->locateDropInCrontab('app'));
    }
}
