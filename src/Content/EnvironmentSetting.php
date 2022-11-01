<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

class EnvironmentSetting implements CrontabLineInterface
{
    public function __construct(
        private string $name = '',
        private string $value = '',
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function parse(string $rawLine): void
    {
        if (!str_contains($rawLine, '=')) {
            throw new \RuntimeException('Environment settings must be in the format name=value.');
        }
        [$this->name, $this->value] = explode('=', $rawLine, 2);
    }

    public function build(): string
    {
        return sprintf('%s=%s', $this->getName(), $this->getValue());
    }
}
