<?php

namespace TurkerExecutor\Connection;

use PDO;

interface ConnectionInterface
{
    public function insert(string $query, array $bindings = []): bool;
    public function update(string $query, array $bindings = []): bool;
    public function delete(string $query, array $bindings = []): bool;
    public function select(string $query, array $bindings = []): array;
    public function statement(string $query, array $bindings = []): bool;
    public function lastInsertId(): string|false;
    public function getPdo(): PDO;
    public function getTablePrefix(): string;
    public function hasTable(string $table): bool;
} 