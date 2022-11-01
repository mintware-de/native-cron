<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Filesystem;

class DebianCrontabFileLocator implements CrontabFileLocatorInterface
{
    public function locateDropInCrontab(string $name): string
    {
        return '/etc/cron.d/'.$name;
    }
}
