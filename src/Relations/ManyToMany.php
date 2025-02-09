<?php

namespace TurkerExecutor\Relations;

use TurkerExecutor\Model\Model;

class ManyToMany extends Relation
{
    protected string $table;
    protected string $relatedKey;

    public function __construct(
        Model $related,
        Model $parent,
        string $table,
        string $foreignKey,
        string $relatedKey
    ) {
        $this->table = $table;
        $this->relatedKey = $relatedKey;
        parent::__construct($related, $parent, $foreignKey);
    }

    protected function addConstraints(): void
    {
        $this->query->join(
            $this->table,
            "{$this->related->getTable()}.{$this->related->getKeyName()}",
            '=',
            "{$this->table}.{$this->relatedKey}"
        )->where("{$this->table}.{$this->foreignKey}", '=', $this->parent->getKey());
    }

    public function get(): array
    {
        return $this->query->get();
    }

    public function attach(int|array $ids): bool
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $records = [];
        foreach ($ids as $id) {
            $records[] = [
                $this->foreignKey => $this->parent->getKey(),
                $this->relatedKey => $id
            ];
        }

        return $this->insertPivotRecords($records);
    }

    public function detach(int|array|null $ids = null): bool
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->foreignKey} = ?";
        $bindings = [$this->parent->getKey()];

        if ($ids !== null) {
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
            $query .= " AND {$this->relatedKey} IN ({$placeholders})";
            $bindings = array_merge($bindings, $ids);
        }

        return $this->parent->getConnection()->delete($query, $bindings);
    }

    protected function insertPivotRecords(array $records): bool
    {
        if (empty($records)) {
            return true;
        }

        $columns = array_keys(reset($records));
        $columnList = implode(', ', $columns);
        $placeholders = [];
        $bindings = [];

        foreach ($records as $record) {
            $placeholders[] = '(' . rtrim(str_repeat('?,', count($record)), ',') . ')';
            foreach ($record as $value) {
                $bindings[] = $value;
            }
        }

        $sql = "INSERT INTO {$this->table} ({$columnList}) VALUES " . implode(', ', $placeholders);
        
        return $this->parent->getConnection()->insert($sql, $bindings);
    }
} 