<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Filesystem;

class DarwinCrontabFileLocator implements CrontabFileLocatorInterface
{

    public function locateDropInCrontab(string $name): string
    {
        throw new \RuntimeException('This platform does not support drop-in cron tabs');
    }

    public function locateUserCrontab(string $username): string
    {
        return '/usr/lib/cron/tabs/'.$username;
    }

    public function locateSystemCrontab(): string
    {
        return '/etc/crontab';
    }
}
