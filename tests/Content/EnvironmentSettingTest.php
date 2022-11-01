<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests\Content;

use MintwareDe\NativeCron\Content\CrontabLineInterface;
use MintwareDe\NativeCron\Content\EnvironmentSetting;
use PHPUnit\Framework\TestCase;

class EnvironmentSettingTest extends TestCase
{
    public function testConstructor(): void
    {
        $setting = new EnvironmentSetting('FOO', 'BAR');
        self::assertEquals('FOO', $setting->getName());
        self::assertEquals('BAR', $setting->getValue());
    }

    public function testInheritance(): void
    {
        $setting = new EnvironmentSetting();
        self::assertInstanceOf(CrontabLineInterface::class, $setting);
    }

    public function testParseFailsInvalidFormat(): void
    {
        $setting = new EnvironmentSetting();

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Environment settings must be in the format name=value.');

        $setting->parse('foo');
    }

    public function testParsePass(): void
    {
        $setting = new EnvironmentSetting();

        $setting->parse('foo=bar');
        self::assertEquals('foo', $setting->getName());
        self::assertEquals('bar', $setting->getValue());
    }

    public function testBuildPass(): void
    {
        $setting = new EnvironmentSetting();
        $setting->parse('foo=bar');
        self::assertEquals('foo=bar', $setting->build());
    }
}
