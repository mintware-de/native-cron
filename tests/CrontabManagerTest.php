<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Tests;

use MintwareDe\NativeCron\Content\BlankLine;
use MintwareDe\NativeCron\Content\CommentLine;
use MintwareDe\NativeCron\Content\CronJobLine;
use MintwareDe\NativeCron\Content\Crontab;
use MintwareDe\NativeCron\CrontabManager;
use MintwareDe\NativeCron\Filesystem\CrontabFileLocatorInterface;
use MintwareDe\NativeCron\Filesystem\FileHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CrontabManagerTest extends TestCase
{
    private CrontabManager $manager;
    private CrontabFileLocatorInterface&MockObject $mockFileLocator;
    private FileHandlerInterface&MockObject $mockFileHandler;

    public function setUp(): void
    {
        $this->mockFileLocator = self::createMock(CrontabFileLocatorInterface::class);
        $this->mockFileHandler = self::createMock(FileHandlerInterface::class);
        $this->manager = new CrontabManager($this->mockFileLocator, $this->mockFileHandler);
    }

    public function testReadNonExistentSystemCrontab(): void
    {
        $this->setupReadSystemCrontab(null);

        $crontab = $this->manager->readSystemCrontab();
        $this->verifyReadEmptyCrontab($crontab, true);
    }

    public function testReadSystemCrontab(): void
    {
        $this->setupReadSystemCrontab("#test system crontab\n\n17 * * * * root my-command\n");

        $crontab = $this->manager->readSystemCrontab();
        $this->verifyCrontab($crontab, true);
    }

    public function testReadNonExistentDropInCrontab(): void
    {
        $this->setupReadDropInCrontab(null);

        $crontab = $this->manager->readDropInCrontab('app');
        $this->verifyReadEmptyCrontab($crontab, true);
    }

    public function testReadDropInCrontab(): void
    {
        $this->setupReadDropInCrontab("#test drop-in crontab\n\n17 * * * * root my-command\n");

        $crontab = $this->manager->readDropInCrontab('app');
        $this->verifyCrontab($crontab, true);
    }

    public function testReadNonExistentUserCrontab(): void
    {
        $this->setupReadUserCrontab('admin', null);
        $crontab = $this->manager->readUserCrontab('admin');
        $this->verifyReadEmptyCrontab($crontab, false);
    }

    public function testReadUserCrontab(): void
    {
        $this->setupReadUserCrontab('admin', "#test system crontab\n\n17 * * * * my-command\n");
        $crontab = $this->manager->readUserCrontab('admin');
        $this->verifyCrontab($crontab, false);
    }

    public function testWriteSystemCrontabFails(): void
    {
        $mockCrontab = self::createMock(Crontab::class);

        $mockCrontab->expects(self::once())
            ->method('isSystemCrontab')
            ->willReturn(false);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('The crontab is not a system crontab.');

        $this->manager->writeSystemCrontab($mockCrontab);
    }

    public function testWriteSystemCrontabPass(): void
    {
        $this->mockFileLocator
            ->expects(self::once())
            ->method('locateSystemCrontab')
            ->willReturn('crontab-file');

        $mockCrontab = self::createMock(Crontab::class);

        $this->setupWriteCrontab($mockCrontab, true, 'root');

        $this->manager->writeSystemCrontab($mockCrontab);
    }

    public function testWriteDropInCrontabFails(): void
    {
        $mockCrontab = self::createMock(Crontab::class);

        $mockCrontab->expects(self::once())
            ->method('isSystemCrontab')
            ->willReturn(false);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('The crontab is not a system crontab.');

        $this->manager->writeDropInCrontab($mockCrontab, 'app');
    }

    public function testWriteDropInCrontabPass(): void
    {
        $this->mockFileLocator
            ->expects(self::once())
            ->method('locateDropInCrontab')
            ->with('app')
            ->willReturn('crontab-file');

        $mockCrontab = self::createMock(Crontab::class);

        $this->setupWriteCrontab($mockCrontab, true, 'root');

        $this->manager->writeDropInCrontab($mockCrontab, 'app');
    }

    public function testWriteUserCrontabFails(): void
    {
        $mockCrontab = self::createMock(Crontab::class);

        $mockCrontab->expects(self::once())
            ->method('isSystemCrontab')
            ->willReturn(true);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('The crontab is a system crontab.');

        $this->manager->writeUserCrontab($mockCrontab, 'app');
    }

    public function testWriteUserCrontabPass(): void
    {
        $this->mockFileLocator
            ->expects(self::once())
            ->method('locateUserCrontab')
            ->with('foo')
            ->willReturn('crontab-file');

        $mockCrontab = self::createMock(Crontab::class);

        $this->setupWriteCrontab($mockCrontab, false, 'foo');

        $this->manager->writeUserCrontab($mockCrontab, 'foo');
    }

    private function verifyReadEmptyCrontab(Crontab $crontab, bool $isSystemCrontab): void
    {
        self::assertInstanceOf(Crontab::class, $crontab);
        self::assertEquals($isSystemCrontab, $crontab->isSystemCrontab());
        self::assertCount(0, $crontab->getLines());
    }

    private function verifyCrontab(Crontab $crontab, bool $isSystemCrontab): void
    {
        self::assertInstanceOf(Crontab::class, $crontab);
        self::assertEquals($isSystemCrontab, $crontab->isSystemCrontab());
        self::assertCount(4, $crontab->getLines());

        self::assertInstanceOf(CommentLine::class, $crontab->getLines()[0]);
        self::assertInstanceOf(BlankLine::class, $crontab->getLines()[1]);
        self::assertInstanceOf(CronJobLine::class, $crontab->getLines()[2]);
        self::assertInstanceOf(BlankLine::class, $crontab->getLines()[3]);
    }

    private function setupReadSystemCrontab(?string $content): void
    {
        $this->mockFileLocator
            ->expects(self::once())
            ->method('locateSystemCrontab')
            ->willReturn('crontab-file');

        $this->mockFileHandler
            ->expects(self::once())
            ->method('read')
            ->with('crontab-file')
            ->willReturn($content);
    }

    private function setupReadDropInCrontab(?string $content): void
    {
        $this->mockFileLocator
            ->expects(self::once())
            ->method('locateDropInCrontab')
            ->willReturn('crontab-file');

        $this->mockFileHandler
            ->expects(self::once())
            ->method('read')
            ->with('crontab-file')
            ->willReturn($content);
    }

    private function setupReadUserCrontab(string $username, ?string $content): void
    {
        $this->mockFileLocator
            ->expects(self::once())
            ->method('locateUserCrontab')
            ->with($username)
            ->willReturn('crontab-file');

        $this->mockFileHandler
            ->expects(self::once())
            ->method('read')
            ->with('crontab-file')
            ->willReturn($content);
    }

    private function setupWriteCrontab(Crontab&MockObject $mockCrontab, bool $isSystemCrontab, string $owner): void
    {
        $mockCrontab->expects(self::once())
            ->method('isSystemCrontab')
            ->willReturn($isSystemCrontab);

        $mockCrontab->expects(self::once())
            ->method('build')
            ->willReturn('crontab-content');

        $this->mockFileHandler
            ->expects(self::once())
            ->method('createFile')
            ->with('crontab-file');

        $this->mockFileHandler
            ->expects(self::once())
            ->method('setPermissions')
            ->with('crontab-file', 0600);

        $this->mockFileHandler
            ->expects(self::once())
            ->method('setOwner')
            ->with('crontab-file', $owner);

        $this->mockFileHandler
            ->expects(self::once())
            ->method('write')
            ->with('crontab-file', 'crontab-content');
    }
}
