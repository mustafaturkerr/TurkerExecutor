<?php

namespace TurkerExecutor\Schema;

class ColumnDefinition
{
    protected array $attributes;

    public function __construct(array &$attributes)
    {
        $this->attributes = &$attributes;
    }

    public function nullable(): self
    {
        $this->attributes['nullable'] = true;
        return $this;
    }

    public function unique(): self
    {
        $this->attributes['unique'] = true;
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->attributes['default'] = $value;
        return $this;
    }

    public function index(): static
    {
        $this->attributes['index'] = true;
        return $this;
    }
} 