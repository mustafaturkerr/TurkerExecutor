<?php

namespace TurkerExecutor\Query;

use TurkerExecutor\Model\Model;

class QueryBuilder
{
    protected Model $model;
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orders = [];
    protected array $joins = [];
    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = compact('table', 'first', 'operator', 'second');
        return $this;
    }

    public function where(string $column, ?string $operator = null, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        
        if ($operator !== 'IN') {
            $this->bindings[] = $value;
        }

        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'IN',
            'value' => "({$placeholders})"
        ];
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->toSql();
        $results = $this->model->getConnection()->select($sql, $this->bindings);
        
        return array_map(function ($attributes) {
            $model = clone $this->model;
            $model->fill((array) $attributes);
            $model->exists = true;
            return $model;
        }, $results);
    }

    public function first(): ?Model
    {
        $results = $this->limit(1)->get();
        if (!empty($results)) {
            $model = $results[0];
            $model->exists = true;
            return $model;
        }
        return null;
    }

    protected function toSql(): string
    {
        $sql = ["SELECT * FROM {$this->model->getTable()}"];

        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql[] = "JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }

        if (!empty($this->wheres)) {
            $sql[] = 'WHERE ' . $this->compileWheres();
        }

        if (!empty($this->orders)) {
            $sql[] = 'ORDER BY ' . $this->compileOrders();
        }

        if ($this->limit !== null) {
            $sql[] = "LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql[] = "OFFSET {$this->offset}";
        }

        return implode(' ', $sql);
    }

    protected function compileWheres(): string
    {
        return implode(' AND ', array_map(function ($where) {
            if ($where['operator'] === 'IN') {
                return "{$where['column']} {$where['operator']} {$where['value']}";
            }
            return "{$where['column']} {$where['operator']} ?";
        }, $this->wheres));
    }

    protected function compileOrders(): string
    {
        return implode(', ', array_map(function ($order) {
            return "{$order['column']} {$order['direction']}";
        }, $this->orders));
    }

    protected function getWheres(): array
    {
        return $this->wheres;
    }

    protected function getBindings(): array
    {
        return $this->bindings;
    }

    protected function setWheres(array $wheres): self
    {
        $this->wheres = $wheres;
        return $this;
    }

    protected function setBindings(array $bindings): self
    {
        $this->bindings = $bindings;
        return $this;
    }
} 