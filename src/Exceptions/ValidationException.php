<?php

namespace TurkerExecutor\Exceptions;

use Exception;

class ValidationException extends Exception
{
    private array $errors = [];

    public static function withErrors(array $errors): self
    {
        $instance = new self("Doğrulama hatası");
        $instance->errors = $errors;
        return $instance;
    }

    public static function invalidValue(string $field, mixed $value): self
    {
        return new self("Geçersiz değer: {$field} için {$value}");
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
} 