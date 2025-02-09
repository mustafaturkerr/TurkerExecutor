<?php

namespace TurkerExecutor\Relations;

use TurkerExecutor\Model\Model;

class HasOne extends Relation
{
    protected function addConstraints(): void
    {
        $this->query->where($this->foreignKey, '=', $this->parent->getKey());
    }

    public function get(): ?Model
    {
        return $this->query->first();
    }

    public function associate(Model $model): Model
    {
        $this->parent->{$this->foreignKey} = $model->getKey();
        return $this->parent;
    }
} 