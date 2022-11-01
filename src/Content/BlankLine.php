<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

use RuntimeException;

class BlankLine implements CrontabLineInterface
{
    public function parse(string $rawLine): void
    {
        if (!empty($rawLine)) {
            throw new RuntimeException('Parsing non-empty lines is not supported.');
        }
    }

    public function build(): string
    {
        return '';
    }
}
