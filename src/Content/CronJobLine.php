<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

use RuntimeException;

class CronJobLine implements CrontabLineInterface
{
    public const PATTERN_WITH_USER = '~^(?P<datetime>'.DateTimeDefinition::PATTERN.')\s*(?P<user>\w+)\s*(?P<command>.+)$~';
    public const PATTERN_WITHOUT_USER = '~^(?P<datetime>'.DateTimeDefinition::PATTERN.')\s*(?P<command>.+)$~';

    private bool $includeUser;

    private ?string $user = null;

    private string $command = '';

    private DateTimeDefinition $dateTimeDefinition;

    public function __construct(?string $line = null, bool $includesUser = false)
    {
        $this->dateTimeDefinition = new DateTimeDefinition();
        $this->includeUser = $includesUser;

        if (!empty($line)) {
            $this->parse($line);
        }
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return DateTimeField[]
     * @deprecated Use getDateTimeDefinition()->getMinutes() instead
     */
    public function getMinutes(): array
    {
        return $this->dateTimeDefinition->getMinutes();
    }

    /**
     * @return DateTimeField[]
     * @deprecated Use getDateTimeDefinition()->getHours() instead
     */
    public function getHours(): array
    {
        return $this->dateTimeDefinition->getHours();
    }

    /**
     * @return DateTimeField[]
     * @deprecated Use getDateTimeDefinition()->getDays() instead
     */
    public function getDays(): array
    {
        return $this->dateTimeDefinition->getDays();
    }

    /**
     * @return DateTimeField[]
     * @deprecated Use getDateTimeDefinition()->getMonths() instead
     */
    public function getMonths(): array
    {
        return $this->dateTimeDefinition->getMonths();
    }

    /**
     * @return DateTimeField[]
     * @deprecated Use getDateTimeDefinition()->getWeekdays() instead
     */
    public function getWeekdays(): array
    {
        return $this->dateTimeDefinition->getWeekdays();
    }

    public function getDateTimeDefinition(): DateTimeDefinition
    {
        return $this->dateTimeDefinition;
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

        $this->dateTimeDefinition->parse($matches['datetime']);
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
        $parts = [$this->dateTimeDefinition->build()];

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
}
