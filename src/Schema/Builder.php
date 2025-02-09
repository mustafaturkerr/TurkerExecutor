<?php

namespace TurkerExecutor\Schema;

use TurkerExecutor\Connection\ConnectionInterface;
use Closure;

class Builder
{
    protected Blueprint $blueprint;

    public function __construct(
        protected ConnectionInterface $connection
    ) {}

    public function create(string $table, Closure $callback): bool
    {
        $this->blueprint = new Blueprint($table);
        $callback($this->blueprint);
        
        return $this->connection->statement($this->blueprint->toSql());
    }

    public function table(string $table, Closure $callback): bool
    {
        $this->blueprint = new Blueprint($table);
        $callback($this->blueprint);
        
        return $this->connection->statement($this->blueprint->toSql());
    }

    public function drop(string $table): bool
    {
        return $this->connection->statement("DROP TABLE IF EXISTS {$table}");
    }

    public function hasTable(string $table): bool
    {
        $table = $this->connection->getTablePrefix() . $table;
        return $this->connection->hasTable($table);
    }
} 