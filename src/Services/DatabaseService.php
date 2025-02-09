<?php

namespace TurkerExecutor\Services;

use Exception;
use TurkerExecutor\Connection\ConnectionInterface;
use TurkerExecutor\Repositories\RepositoryInterface;

class DatabaseService
{

    public function __construct(
        private ConnectionInterface $connection,
        RepositoryInterface $repository
    ) {}

    public function beginTransaction(): bool
    {
        return $this->connection->getPdo()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->getPdo()->commit();
    }

    public function rollBack(): bool
    {
        return $this->connection->getPdo()->rollBack();
    }

    /**
     * @throws Exception
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
} 