<?php

declare(strict_types=1);

/**
 * Small reusable server-side validation helpers.
 * Collects field => message errors so callers can return them all at once.
 */
class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $label): self
    {
        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field] = "$label is required";
        }

        return $this;
    }

    public function exactDigits(string $field, mixed $value, int $length, string $label): self
    {
        if ($value !== null && $value !== '' && !preg_match('/^\d{' . $length . '}$/', (string) $value)) {
            $this->errors[$field] = "$label must contain exactly $length digits";
        }

        return $this;
    }

    public function numeric(string $field, mixed $value, string $label): self
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = "$label must be a number";
        }

        return $this;
    }

    public function greaterThan(string $field, mixed $value, float $min, string $label): self
    {
        if ($value !== null && $value !== '' && is_numeric($value) && (float) $value <= $min) {
            $this->errors[$field] = "$label must be greater than $min";
        }

        return $this;
    }

    public function in(string $field, mixed $value, array $allowed, string $label): self
    {
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = "$label must be one of: " . implode(', ', $allowed);
        }

        return $this;
    }

    public function maxLength(string $field, mixed $value, int $max, string $label): self
    {
        if ($value !== null && mb_strlen((string) $value) > $max) {
            $this->errors[$field] = "$label must not exceed $max characters";
        }

        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
