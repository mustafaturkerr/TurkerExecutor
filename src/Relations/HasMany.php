<?php

namespace TurkerExecutor\Relations;

class HasMany extends Relation
{
    public function __construct($related, $parent, $foreignKey)
    {
        parent::__construct($related, $parent, $foreignKey);
    }

    protected function addConstraints(): void
    {
        $this->query->where($this->foreignKey, '=', $this->parent->getKey());
    }

    public function get(): array
    {
        return $this->query->get();
    }

    public function where($column, $operator = null, $value = null): static
    {
        $this->query->where($column, $operator, $value);
        return $this;
    }

    public function orderBy($column, $direction = 'asc'): static
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }
} 