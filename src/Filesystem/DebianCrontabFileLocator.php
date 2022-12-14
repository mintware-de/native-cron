<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Filesystem;

class DebianCrontabFileLocator implements CrontabFileLocatorInterface
{
    public function locateDropInCrontab(string $name): string
    {
        return '/etc/cron.d/'.$name;
    }

    public function locateUserCrontab(string $username): string
    {
        return '/var/spool/cron/'.$username;
    }

    public function locateSystemCrontab(): string
    {
        return '/etc/crontab';
    }
}
