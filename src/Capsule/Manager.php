<?php

namespace TurkerExecutor\Capsule;

use TurkerExecutor\Connection\ConnectionInterface;
use TurkerExecutor\Schema\Builder;

class Manager
{
    protected ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    public function schema(): Builder
    {
        return new Builder($this->connection);
    }
}