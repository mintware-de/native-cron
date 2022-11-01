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

    /**
     * Returns the path to the user crontab file.
     * This file is usually located at /var/spool/cron/$username
     */
    public function locateUserCrontab(string $username): string;

    /**
     * Returns the path to the crontab file.
     * This file is usually located at /etc/crontab
     */
    public function locateSystemCrontab(): string;
}
