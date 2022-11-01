<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Filesystem;

interface FileHandlerInterface
{
    public function createFile(string $filename): void;

    public function setPermissions(string $filename, int $permissions): void;

    public function setOwner(string $filename, string $owner): void;

    public function read(string $filename): ?string;

    public function write(string $filename, string $contents): void;
}
