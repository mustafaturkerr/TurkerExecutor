<?php

namespace TurkerExecutor\Model;

use BadMethodCallException;
use RuntimeException;
use TurkerExecutor\TurkerExecutor;
use TurkerExecutor\Query\QueryBuilder;
use TurkerExecutor\Relations\HasMany;
use TurkerExecutor\Relations\HasOne;
use TurkerExecutor\Relations\BelongsTo;
use TurkerExecutor\Relations\ManyToMany;
use TurkerExecutor\Relations\Relation;
use TurkerExecutor\Connection\ConnectionInterface;
use TurkerExecutor\Support\Str;
use TurkerExecutor\Model\Traits\HasEvents;
use TurkerExecutor\Exceptions\ModelException;

abstract class Model
{
    use HasEvents;

    protected string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];
    protected ConnectionInterface $connection;
    public bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->connection = TurkerExecutor::getInstance()->getCapsule()->getConnection();
        $this->fill($attributes);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getKey(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        if ($model->save()) {
            $model->exists = true;
            return $model;
        }
        throw ModelException::updateFailed(static::class, 0);
    }

    public function update(array $attributes): bool
    {
        if (!$this->exists) {
            throw ModelException::updateFailed(static::class, $this->getKey());
        }

        if (!$this->fireUpdating()) {
            return false;
        }

        $this->fill($attributes);
        $result = $this->save();

        if ($result) {
            $this->fireUpdated();
        }

        return $result;
    }

    public function syncOriginal(): self
    {
        $this->original = $this->attributes;
        return $this;
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            throw ModelException::deleteFailed(static::class, $this->getKey());
        }

        if (!$this->fireDeleting()) {
            return false;
        }

        $result = $this->performDelete();

        if ($result) {
            $this->fireDeleted();
        }

        return $result;
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key): mixed
    {
        $value = $this->attributes[$key] ?? null;
        
        if ($key === $this->primaryKey && $value !== null) {
            return (int)$value;
        }
        
        return $value;
    }

    public function save(): bool
    {
        if (!$this->fireSaving()) {
            return false;
        }

        $result = $this->exists ? $this->performUpdate() : $this->performInsert();

        if ($result) {
            $this->fireSaved();
        }

        return $result;
    }

    protected function performInsert(): bool
    {
        if (empty($this->attributes)) {
            return false;
        }

        $table = $this->table;
        $columns = implode(', ', array_keys($this->attributes));
        $values = implode(', ', array_fill(0, count($this->attributes), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
        
        $result = $this->connection->insert($sql, array_values($this->attributes));
        
        if ($result) {
            $this->exists = true;
            $id = $this->connection->lastInsertId();
            if ($id) {
                $this->setAttribute($this->primaryKey, (int)$id);
                $this->syncOriginal();
            }
        }
        
        return $result;
    }

    protected function performUpdate(): bool
    {
        $table = $this->table;
        $sets = [];
        $bindings = [];

        foreach ($this->attributes as $key => $value) {
            if ($key !== $this->primaryKey) {
                $sets[] = "{$key} = ?";
                $bindings[] = $value;
            }
        }

        $bindings[] = $this->getKey();

        $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?";
        
        return $this->connection->update($sql, $bindings);
    }

    protected function performDelete(): bool
    {
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->connection->delete($query, [$this->getKey()]);
    }

    public static function where(string $column, string $operator = null, mixed $value = null): QueryBuilder
    {
        $query = new QueryBuilder(new static);
        return $query->where($column, $operator, $value);
    }

    public static function whereIn(string $column, array $values): QueryBuilder
    {
        return (new QueryBuilder(new static))->whereIn($column, $values);
    }

    public static function orderBy(string $column, string $direction = 'asc'): QueryBuilder
    {
        return (new QueryBuilder(new static))->orderBy($column, $direction);
    }

    public static function first(): ?static
    {
        return (new QueryBuilder(new static))->first();
    }

    public static function find(int $id): ?static
    {
        $model = static::where(static::make()->getKeyName(), '=', $id)->first();
        if (!$model) {
            throw ModelException::notFound(static::class, $id);
        }
        return $model;
    }

    public static function findOrFail(int $id): static
    {
        $result = static::find($id);
        if (!$result) {
            throw new RuntimeException("Model not found.");
        }
        return $result;
    }

    public static function all(): array
    {
        return (new QueryBuilder(new static))->get();
    }

    protected static function make(): static
    {
        return new static;
    }

    public function hasOne(string $related, string $foreignKey = null): HasOne
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $this->table . '_id';
        
        return new HasOne($instance, $this, $foreignKey);
    }

    public function hasMany(string $related, string $foreignKey = null): HasMany
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: strtolower(Str::classBasename($this)) . '_id';
        
        return new HasMany($instance, $this, $foreignKey);
    }

    public function belongsTo(string $related, string $foreignKey = null): BelongsTo
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: strtolower(Str::classBasename($related)) . '_id';
        
        return new BelongsTo($instance, $this, $foreignKey);
    }

    public function belongsToMany(string $related, string $table = null, string $foreignKey = null, string $relatedKey = null): ManyToMany
    {
        $instance = new $related;
        
        $table = $table ?: $this->joiningTable($instance);
        $foreignKey = $foreignKey ?: $this->table . '_id';
        $relatedKey = $relatedKey ?: $instance->table . '_id';
        
        return new ManyToMany($instance, $this, $table, $foreignKey, $relatedKey);
    }

    protected function joiningTable(Model $related): string
    {
        $models = [
            $this->table,
            $related->getTable()
        ];
        sort($models);
        
        return implode('_', $models);
    }


    public function __get(string $key): mixed
    {
        $attribute = $this->getAttribute($key);
        if ($attribute !== null) {
            return $attribute;
        }

        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof Relation) {
                return $relation->get();
            }
            return $relation;
        }

        return null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public static function __callStatic(string $method, array $parameters): mixed
    {
        $instance = new static;

        if (method_exists($instance, 'scope'.ucfirst($method))) {
            $query = new QueryBuilder($instance);
            return $instance->{'scope'.ucfirst($method)}($query, ...$parameters);
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }
}