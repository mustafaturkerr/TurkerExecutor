<?php

namespace TurkerExecutor\Connection;

use PDO;
use PDOException;

class SQLiteConnection implements ConnectionInterface
{
    protected PDO $pdo;
    protected array $config;
    protected string $tablePrefix = '';

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
        if (isset($config['prefix'])) {
            $this->tablePrefix = $config['prefix'];
        }
    }

    protected function connect(): void
    {
        $this->pdo = new PDO("sqlite:{$this->config['database']}");
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insert(string $query, array $bindings = []): bool
    {
        return $this->statement($query, $bindings);
    }

    public function update(string $query, array $bindings = []): bool
    {
        try {
            $statement = $this->pdo->prepare($query);
            $result = $statement->execute($bindings);
            return $result && $statement->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("SQL Error: " . $e->getMessage());
        }
    }

    public function delete(string $query, array $bindings = []): bool
    {
        return $this->statement($query, $bindings);
    }

    public function select(string $query, array $bindings = []): array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function statement(string $query, array $bindings = []): bool
    {
        $statement = $this->pdo->prepare($query);
        return $statement->execute($bindings);
    }

    public function lastInsertId(): string|false
    {
        return $this->pdo->lastInsertId();
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    public function hasTable(string $table): bool
    {
        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name = ?";
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$table]);
        return (bool) $statement->fetch();
    }
}