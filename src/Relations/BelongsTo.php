<?php

namespace TurkerExecutor\Relations;

use TurkerExecutor\Query\QueryBuilder;
use TurkerExecutor\Model\Model;

class BelongsTo extends Relation
{
    public function __construct($related, Model $parent, string $foreignKey)
    {
        parent::__construct($related, $parent, $foreignKey);
        $this->query = new QueryBuilder($related);
    }

    protected function addConstraints(): void
    {
        $this->query->where($this->related->getKeyName(), '=', $this->parent->getAttribute($this->foreignKey));
    }

    public function get(): ?Model
    {
        return $this->query->first();
    }

    public function __get(string $key): mixed
    {
        return $this->get()?->{$key};
    }

    public function associate(Model $model): Model
    {
        $this->parent->{$this->foreignKey} = $model->getKey();
        return $this->parent;
    }
} 