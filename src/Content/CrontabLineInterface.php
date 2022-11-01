<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

/** This is a marker interface for crontab lines */
interface CrontabLineInterface
{
    /**
     * Parses the raw line from the crontab file.
     */
    public function parse(string $rawLine): void;

    /**
     * Build the line object to a string for storing it in the real crontab file.
     */
    public function build(): string;
}
