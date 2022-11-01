<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Filesystem;

interface CrontabFileLocatorInterface
{
    /**
     * Returns the path to a drop-in crontab file.
     * These files are usually located at /etc/cron.d/$name
     */
    public function locateDropInCrontab(string $name): string;
}
