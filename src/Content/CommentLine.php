<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

/**
 * Represents a comment line
 */
class CommentLine implements CrontabLineInterface
{
    private string $comment = '';

    public function build(): string
    {
        return '#'.$this->comment;
    }

    public function parse(string $rawLine): void
    {
        if (!str_starts_with($rawLine, '#')) {
            throw new \RuntimeException('A comment line must start with a # character.');
        }

        $this->comment = substr($rawLine, 1);
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
