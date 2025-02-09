<?php

namespace TurkerExecutor\Schema;

use TurkerExecutor\Schema\Grammar\Grammar;
use TurkerExecutor\Schema\Grammar\MySqlGrammar;
use TurkerExecutor\Schema\Grammar\SQLiteGrammar;
use TurkerExecutor\TurkerExecutor;

class Blueprint
{
    protected array $columns = [];
    protected Grammar $grammar;

    public function __construct(protected string $table)
    {
        $connection = TurkerExecutor::getInstance()->getCapsule()->getConnection();
        $this->grammar = match(true) {
            $connection instanceof \TurkerExecutor\Connection\MySQLConnection => new MySqlGrammar(),
            default => new SQLiteGrammar(),
        };
    }

    public function id(): ColumnDefinition
    {
        return $this->increments('id');
    }

    public function increments(string $column): ColumnDefinition
    {
        return $this->addColumn('increments', $column);
    }

    public function string(string $column, int $length = 255): ColumnDefinition
    {
        return $this->addColumn('string', $column, compact('length'));
    }

    public function integer(string $column): ColumnDefinition
    {
        return $this->addColumn('integer', $column);
    }

    public function boolean(string $column): ColumnDefinition
    {
        return $this->addColumn('boolean', $column);
    }

    public function bool(string $column): ColumnDefinition
    {
        return $this->boolean($column);
    }

    public function text(string $column): ColumnDefinition
    {
        return $this->addColumn('text', $column);
    }

    public function timestamp(string $column): ColumnDefinition
    {
        return $this->addColumn('timestamp', $column);
    }

    public function timestamps(): void
    {
        $this->timestamp('created_at')->nullable()->default('CURRENT_TIMESTAMP');
        $this->timestamp('updated_at')->nullable()->default('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    protected function addColumn(string $type, string $name, array $parameters = []): ColumnDefinition
    {
        $column = [
            'type' => $type,
            'name' => $name,
            'parameters' => $parameters,
            'attributes' => [],
        ];

        $this->columns[] = $column;
        return new ColumnDefinition($this->columns[array_key_last($this->columns)]['attributes']);
    }

    public function toSql(): string
    {
        $sql = "CREATE TABLE {$this->table} (";
        
        $columnDefinitions = [];
        foreach ($this->columns as $column) {
            $def = $this->compileColumn($column);
            $columnDefinitions[] = $def;
        }
        
        $sql .= implode(', ', $columnDefinitions);
        $sql .= ")" . $this->grammar->getTableSuffix();
        
        return $sql;
    }

    protected function compileColumn(array $column): string
    {
        $def = "{$column['name']} {$this->getColumnType($column)}";
            
        if (isset($column['attributes']['unique']) && $column['attributes']['unique']) {
            $def .= " UNIQUE";
        }

        if (!isset($column['attributes']['nullable']) || !$column['attributes']['nullable']) {
            $def .= " NOT NULL";
        }

        if (isset($column['attributes']['default'])) {
            $value = $column['attributes']['default'];
            if ($column['type'] === 'timestamp') {
                $def .= " DEFAULT " . $this->grammar->wrapDefaultTimestamp($value);
            } else {
                $def .= " DEFAULT " . $this->getDefaultValue($value);
            }
        }

        return $def;
    }

    protected function getDefaultValue(mixed $value): string
    {
        return match(true) {
            is_bool($value) => $value ? '1' : '0',
            is_string($value) => "'$value'",
            is_null($value) => 'NULL',
            default => (string)$value
        };
    }

    protected function getColumnType(array $column): string
    {
        return match($column['type']) {
            'increments' => $this->grammar->getIncrementsType(),
            'string' => 'VARCHAR(' . ($column['parameters']['length'] ?? 255) . ')',
            'integer' => $this->grammar->getIntegerType(),
            'boolean' => $this->grammar->getBooleanType(),
            'text' => 'TEXT',
            'timestamp' => $this->grammar->getTimestampType(),
            default => 'VARCHAR(255)',
        };
    }
} 