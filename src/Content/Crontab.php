<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

class Crontab
{
    /** @var CrontabLineInterface[] */
    private array $lines = [];

    public function __construct(
        private bool $isSystemCrontab = true,
    ) {
    }

    /**
     * @return CrontabLineInterface[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function setIsSystemCrontab(bool $isSystemCrontab): self
    {
        $this->isSystemCrontab = $isSystemCrontab;
        foreach ($this->lines as $line) {
            if ($line instanceof CronJobLine) {
                $line->setIncludeUser($isSystemCrontab);
            }
        }

        return $this;
    }

    public function isSystemCrontab(): bool
    {
        return $this->isSystemCrontab;
    }

    /**
     * Add a new line to the crontab.
     */
    public function add(CrontabLineInterface $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    /**
     * Remove a specific line from the crontab.
     */
    public function remove(CrontabLineInterface $line): self
    {
        $index = array_search($line, $this->lines);
        if (is_int($index)) {
            array_splice($this->lines, $index, 1);
        }

        return $this;
    }

    /**
     * Remove lines that match the filter expression from the crontab.
     *
     * @param callable(CrontabLineInterface): boolean $filter
     *
     * @return Crontab
     */
    public function removeWhere(callable $filter): self
    {
        $indices = [];
        foreach ($this->lines as $index => $line) {
            if ($filter($line)) {
                $indices[] = $index;
            }
        }
        sort($indices);
        foreach (array_reverse($indices) as $index) {
            array_splice($this->lines, $index, 1);
        }

        return $this;
    }

    /**
     * Parse the content of a crontab file
     */
    public function parse(string $content): void
    {
        $rawLines = array_map('ltrim', explode("\n", $content));
        $lines = [];
        foreach ($rawLines as $rawLine) {
            $line = null;
            if (str_starts_with($rawLine, '#')) {
                $line = new CommentLine();
            } elseif (empty($rawLine)) {
                $line = new BlankLine();
            } elseif (preg_match(CronJobLine::PATTERN_WITHOUT_USER, $rawLine)) {
                $line = new CronJobLine(null, $this->isSystemCrontab());
            } elseif (str_contains($rawLine, '=')) {
                $line = new EnvironmentSetting();
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
