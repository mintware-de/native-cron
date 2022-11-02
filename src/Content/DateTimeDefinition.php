<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

class DateTimeDefinition
{
    public const PATTERN = '(?P<minute>[\d\-,\*\/]+)\s*(?P<hour>[\d\-,\*\/]+)\s*(?P<day>[\d\-,\*\/]+)\s*(?P<month>[\d\-,\*\/]+)\s*(?P<weekday>[\d\-,\*\/]+)';

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

    public function __construct()
    {
        $this->parse('* * * * *');
    }

    public function setMinutes(string $minutes): self
    {
        $this->minutes = $this->parseDateTimeField($minutes, 0, 59);

        return $this;
    }

    public function setHours(string $hours): self
    {
        $this->hours = $this->parseDateTimeField($hours, 0, 23);

        return $this;
    }

    public function setDays(string $days): self
    {
        $this->days = $this->parseDateTimeField($days, 1, 31);

        return $this;
    }

    public function setMonths(string $months): self
    {
        $this->months = $this->parseDateTimeField($months, 1, 12);

        return $this;
    }

    public function setWeekdays(string $weekdays): self
    {
        $this->weekdays = $this->parseDateTimeField($weekdays, 0, 6);

        return $this;
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

    /**
     * Parses the string representation.
     * Example values:
     * - *   *      * * *
     * - 0   0      1 1 0
     * - 1-2 1-12/2 * * *
     */
    public function parse(string $string): void
    {
        if (!preg_match('~'.self::PATTERN.'~', $string, $matches)) {
            throw new \RuntimeException('The date time definition string does not match the expected format.');
        }

        $this
            ->setMinutes($matches['minute'])
            ->setHours($matches['hour'])
            ->setDays($matches['day'])
            ->setMonths($matches['month'])
            ->setWeekdays($matches['weekday']);
    }

    /**
     * Converts this object to the string representation.
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

        return implode(' ', $parts);
    }
}
