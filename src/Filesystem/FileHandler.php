<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Filesystem;

class FileHandler implements FileHandlerInterface
{
    public function createFile(string $filename): void
    {
        if (is_file($filename)) {
            return;
        }

        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        touch($filename);
    }

    public function setPermissions(string $filename, int $permissions): void
    {
        chmod($filename, $permissions);
    }

    public function setOwner(string $filename, string $owner): void
    {
        chown($filename, $owner);
    }

    public function read(string $filename): ?string
    {
        if (!is_file($filename)) {
            return null;
        }

        if (!is_readable($filename)) {
            throw new \RuntimeException('The file is not readable.');
        }

        $content = file_get_contents($filename);
//        if ($content === false) {
//            throw new \RuntimeException('Error while reading the file.');
//        }

        return $content;
    }

    public function write(string $filename, string $contents): void
    {
        if ( !is_writable($filename)) {
            throw new \RuntimeException('The file is not writable.');
        }

        file_put_contents($filename, $contents);
    }
}
