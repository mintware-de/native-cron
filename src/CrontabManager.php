<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron;

use MintwareDe\NativeCron\Content\Crontab;
use MintwareDe\NativeCron\Filesystem\CrontabFileLocatorInterface;
use MintwareDe\NativeCron\Filesystem\FileHandlerInterface;

class CrontabManager
{
    public function __construct(
        private readonly CrontabFileLocatorInterface $fileLocator,
        private readonly FileHandlerInterface $fileHandler,
    ) {
    }

    /**
     * Reads the system-wide crontab file.
     *
     * @return Crontab
     */
    public function readSystemCrontab(): Crontab
    {
        $crontabFile = $this->fileLocator->locateSystemCrontab();

        return $this->readCrontabInternal($crontabFile, true);
    }

    /**
     * Reads the crontab file for a user.
     *
     * @param string $username The username of the user
     *
     * @return Crontab
     */
    public function readUserCrontab(string $username): Crontab
    {
        $crontabFile = $this->fileLocator->locateUserCrontab($username);

        return $this->readCrontabInternal($crontabFile, false);
    }

    /**
     * Reads a drop-in crontab file.
     *
     * @param string $name The name of the drop-in
     *
     * @return Crontab
     */
    public function readDropInCrontab(string $name): Crontab
    {
        $crontabFile = $this->fileLocator->locateDropInCrontab($name);

        return $this->readCrontabInternal($crontabFile, true);
    }

    private function readCrontabInternal(string $crontabFile, bool $isSystemCrontab): Crontab
    {
        $crontabContent = $this->fileHandler->read($crontabFile);

        $crontab = new Crontab($isSystemCrontab);
        if ($crontabContent !== null) {
            $crontab->parse($crontabContent);
        }

        return $crontab;
    }

    public function writeSystemCrontab(Crontab $crontab): void
    {
        $crontabFile = $this->fileLocator->locateSystemCrontab();

        $this->writeCrontabFileInternal($crontabFile, $crontab, 'root', true);
    }

    public function writeDropInCrontab(Crontab $crontab, string $name): void
    {
        $crontabFile = $this->fileLocator->locateDropInCrontab($name);

        $this->writeCrontabFileInternal($crontabFile, $crontab, 'root', true);
    }

    public function writeUserCrontab(Crontab $crontab, string $username): void
    {
        $crontabFile = $this->fileLocator->locateUserCrontab($username);

        $this->writeCrontabFileInternal($crontabFile, $crontab, $username, false);
    }

    private function writeCrontabFileInternal(
        string $crontabFile,
        Crontab $crontab,
        string $owner,
        bool $isSystemFile
    ): void {
        $isSystemCrontab = $crontab->isSystemCrontab();
        if ($isSystemCrontab != $isSystemFile) {
            if ($isSystemCrontab) {
                throw new \RuntimeException('The crontab is a system crontab.');
            } else {
                throw new \RuntimeException('The crontab is not a system crontab.');
            }
        }

        $this->fileHandler->createFile($crontabFile);
        $this->fileHandler->setPermissions($crontabFile, 0600);
        $this->fileHandler->setOwner($crontabFile, $owner);

        $crontabContent = $crontab->build();
        $this->fileHandler->write($crontabFile, $crontabContent);
    }
}
