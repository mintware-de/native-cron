<?php

declare(strict_types=1);

namespace MintwareDe\NativeCron\Content;

use OutOfBoundsException;

class DateTimeField
{
    private int $valueFrom;
    private int $valueTo;
    private int $step = 1;

    /**
     * @param int                  $min
     * @param int                  $max
     * @param array<string, mixed> $abbreviations Values for abbreviations such as jan,feb or mon-fri.
     */
    public function __construct(
        private readonly int $min,
        private readonly int $max,
        private readonly array $abbreviations = [],
    ) {
        $this->valueFrom = $min;
        $this->valueTo = $max;
    }

    /**
     * Returns the minimum value for this field.
     */
    public function getMin(): int
    {
        return $this->min;
    }

    /**
     * Returns the maximum value for this field.
     */
    public function getMax(): int
    {
        return $this->max;
    }

    /**
     * Returns the step for this field.
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * Returns the from-value.
     */
    public function getValueFrom(): int
    {
        return $this->valueFrom;
    }

    /**
     * Returns the to-value
     */
    public function getValueTo(): int
    {
        return $this->valueTo;
    }

    /**
     * Returns true if the current value is a range.
     */
    public function isRange(): bool
    {
        return $this->valueFrom !== $this->valueTo;
    }

    /**
     * Sets the value to a specific value
     */
    public function setValue(int $value): self
    {
        return $this->setRangeValue($value, $value);
    }

    /**
     * Sets the value to a range.
     */
    public function setRangeValue(int $min, int $max): self
    {
        if ($min < $this->min) {
            throw new \OutOfBoundsException(sprintf('%d is not in the range %d to %d.', $min, $this->min, $this->max));
        }
        if ($max > $this->max) {
            throw new \OutOfBoundsException(sprintf('%d is not in the range %d to %d.', $max, $this->min, $this->max));
        }
        $this->valueFrom = $min;
        $this->valueTo = $max;

        return $this;
    }

    /**
     * Unset the value which means that any value between min and max is active.
     */
    public function unsetValue(): self
    {
        $this->valueFrom = $this->min;
        $this->valueTo = $this->max;

        return $this;
    }

    /**
     * Returns true if a specific value is set.
     */
    public function hasValue(): bool
    {
        return !$this->isRange()
            || !($this->min === $this->valueFrom && $this->max === $this->valueTo);
    }

    /**
     * Build the crontab string representation of this object.
     */
    public function build(): string
    {
        if (!$this->hasValue()) {
            $val = '*';
        } elseif ($this->isRange()) {
            $val = sprintf('%d-%d', $this->valueFrom, $this->getValueTo());
        } else {
            $val = strval($this->valueFrom);
        }

        if ($this->getStep() != 1) {
            $val = sprintf('%s/%s', $val, $this->getStep());
        }

        return $val;
    }

    /**
     * Sets the step of this field
     */
    public function setStep(int $step): self
    {
        if ($step < 1) {
            throw new OutOfBoundsException('The step must be greater than 0.');
        }
        $this->step = $step;

        return $this;
    }

    /**
     * Parses the string representation and set the properties on this object.
     * Example values:
     * - *:      any value in the range
     * - 1:      1
     * - 1-3:    1, 2 and 3
     * - 0-59/2: every even number from 0 to 59 (0, 2, 4...)
     */
    public function parse(string $string): self
    {
        if (!str_contains($string, '/')) {
            $string .= '/1';
        }
        [$value, $step] = explode('/', $string, 2);
        if (array_key_exists($value, $this->abbreviations)) {
            $value = strval($this->abbreviations[$value]);
        }
        if ($value == '*') {
            $this->unsetValue();
        } elseif (str_contains($value, '-')) {
            [$from, $to] = array_map(function ($part) {
                if (array_key_exists($part, $this->abbreviations)) {
                    $part = $this->abbreviations[$part];
                }

                return intval($part);
            }, explode('-', $value, 2));
            $this->setRangeValue($from, $to);
        } elseif (is_numeric($value)) {
            $this->setValue(intval($value));
        }
        $this->setStep(intval($step));

        return $this;
    }
}
