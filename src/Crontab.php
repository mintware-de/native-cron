<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron;

class Crontab
{
    /** @var CrontabLineInterface[] */
    private array $lines = [];

    /**
     * @return CrontabLineInterface[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function parse(string $content): void
    {
        $rawLines = explode("\n", $content);
        $lines = [];
        foreach ($rawLines as $rawLine) {
            $line = null;
            if (str_starts_with($rawLine, '#')) {
                $line = new CommentLine();
            } elseif (empty($rawLine)) {
                $line = new BlankLine();
            } else {
                $line = new CronjobLine();
            }
            if ($line instanceof CrontabLineInterface) {
                $line->parse($rawLine);
                $lines[] = $line;
            }
        }
        $this->lines = $lines;
    }

    public function build(): string
    {
        return implode("\n", array_map(fn (CrontabLineInterface $l) => $l->build(), $this->lines));
    }
}
