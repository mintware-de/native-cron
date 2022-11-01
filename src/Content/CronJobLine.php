<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

use RuntimeException;

class CronJobLine implements CrontabLineInterface
{
    public const PATTERN_WITH_USER = '~^(?P<minute>[\d\-,\*\/]+)\s*(?P<hour>[\d\-,\*\/]+)\s*(?P<day>[\d\-,\*\/]+)\s*(?P<month>[\d\-,\*\/]+)\s*(?P<weekday>[\d\-,\*\/]+)\s*(?P<user>\w+)\s*(?P<command>.+)$~';
    public const PATTERN_WITHOUT_USER = '~^(?P<minute>[\d\-,\*\/]+)\s*(?P<hour>[\d\-,\*\/]+)\s*(?P<day>[\d\-,\*\/]+)\s*(?P<month>[\d\-,\*\/]+)\s*(?P<weekday>[\d\-,\*\/]+)\s*(?P<command>.+)$~';

    private bool $includeUser;

    private ?string $user = null;

    private string $command = '';

    /** @var DateTimeField[] */
    private array $minutes = [];

    /** @var DateTimeField[] */
    private array $hours = [];

    /** @var DateTimeField[] */
    private array $days = [];

    /** @var DateTimeField[] */
    private array $months = [];

    /** @var DateTimeField[] */
    private array $weekdays = [];

    public function __construct(?string $line = null, bool $includesUser = false)
    {
        $this->includeUser = $includesUser;

        if (!empty($line)) {
            $this->parse($line);
        } else {
            $this->minutes = $this->parseDateTimeField('*', 0, 59);
            $this->hours = $this->parseDateTimeField('*', 0, 23);
            $this->days = $this->parseDateTimeField('*', 1, 31);
            $this->months = $this->parseDateTimeField('*', 1, 12);
            $this->weekdays = $this->parseDateTimeField('*', 0, 6);
        }
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return DateTimeField[]
     */
    public function getMinutes(): array
    {
        return $this->minutes;
    }

    /**
     * @return DateTimeField[]
     */
    public function getHours(): array
    {
        return $this->hours;
    }

    /**
     * @return DateTimeField[]
     */
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @return DateTimeField[]
     */
    public function getMonths(): array
    {
        return $this->months;
    }

    /**
     * @return DateTimeField[]
     */
    public function getWeekdays(): array
    {
        return $this->weekdays;
    }

    public function setUser(?string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getIncludeUser(): bool
    {
        return $this->includeUser;
    }

    public function setIncludeUser(bool $includeUser): void
    {
        $this->includeUser = $includeUser;
    }

    public function parse(string $rawLine): void
    {
        $pattern = $this->includeUser ? self::PATTERN_WITH_USER : self::PATTERN_WITHOUT_USER;

        preg_match($pattern, $rawLine, $matches);
        $this->minutes = $this->parseDateTimeField($matches['minute'], 0, 59);
        $this->hours = $this->parseDateTimeField($matches['hour'], 0, 23);
        $this->days = $this->parseDateTimeField($matches['day'], 1, 31);
        $this->months = $this->parseDateTimeField($matches['month'], 1, 12);
        $this->weekdays = $this->parseDateTimeField($matches['weekday'], 0, 6);
        $this->command = $matches['command'];

        if ($this->includeUser) {
            $this->user = $matches['user'];
        }
    }

    /**
     * @throws RuntimeException if the cron job line requires a user but none was set.
     */
    public function build(): string
    {
        $parts = [
            implode(',', array_map(fn (DateTimeField $o) => $o->build(), $this->minutes)),
            implode(',', array_map(fn (DateTimeField $o) => $o->build(), $this->hours)),
            implode(',', array_map(fn (DateTimeField $o) => $o->build(), $this->days)),
            implode(',', array_map(fn (DateTimeField $o) => $o->build(), $this->months)),
            implode(',', array_map(fn (DateTimeField $o) => $o->build(), $this->weekdays)),
        ];

        if ($this->includeUser) {
            $user = $this->getUser();
            if (empty($user)) {
                throw new RuntimeException('The cron job line requires a user.');
            }
            $parts[] = $user;
        }

        $parts[] = $this->command;

        return implode(' ', $parts);
    }

    /**
     * @return DateTimeField[]
     */
    private function parseDateTimeField(string $fieldString, int $min, int $max): array
    {
        $fields = [];
        $entries = explode(',', $fieldString);
        foreach ($entries as $entry) {
            $field = new DateTimeField($min, $max);
            $field->parse($entry);
            $fields[] = $field;
        }

        return $fields;
    }
}
