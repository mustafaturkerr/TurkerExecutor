<?php

namespace TurkerExecutor\Validation;

class Validator
{
    private array $rules = [];
    private array $errors = [];
    private array $data = [];

    public function addRule(string $field, string $rule, callable $validator): void
    {
        $this->rules[$field][$rule] = $validator;
    }

    public function validate(array $data): bool
    {
        $this->errors = [];
        $this->data = $data;
        foreach ($this->rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule => $validator) {
                if (!isset($data[$field])) {
                    $this->errors[$field][] = "Field {$field} is required";
                    continue;
                }

                if (!$validator($data[$field])) {
                    $this->errors[$field][] = "Field {$field} failed {$rule} validation";
                }
            }
        }

        return empty($this->errors);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
} 