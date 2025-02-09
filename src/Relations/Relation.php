<?php

namespace TurkerExecutor\Relations;

use TurkerExecutor\Model\Model;
use TurkerExecutor\Query\QueryBuilder;

abstract class Relation
{
    protected Model $related;
    protected QueryBuilder $query;
    protected Model $parent;
    protected string $foreignKey;

    public function __construct(Model $related, Model $parent, string $foreignKey)
    {
        $this->related = $related;
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->query = new QueryBuilder($related);
        
        $this->addConstraints();
    }
    
    abstract protected function addConstraints(): void;
    
    abstract public function get(): mixed;
} 