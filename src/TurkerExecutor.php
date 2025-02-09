<?php

namespace TurkerExecutor;

use InvalidArgumentException;
use TurkerExecutor\Connection\SQLiteConnection;
use TurkerExecutor\Connection\MySQLConnection;
use TurkerExecutor\Capsule\Manager;
use TurkerExecutor\Connection\ConnectionInterface;

class TurkerExecutor
{
    private static ?self $instance = null;
    private Manager $capsule;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createCapsule(array $config): Manager
    {
        if (!isset($config['driver'])) {
            throw new InvalidArgumentException('Database driver must be specified.');
        }

        $defaultConfig = [
            'prefix' => '',
        ];

        $connection = match($config['driver']) {
            'mysql' => $this->createMySQLConnection($config + $defaultConfig),
            'sqlite' => $this->createSQLiteConnection($config + $defaultConfig),
            default => throw new InvalidArgumentException('Unsupported database driver.'),
        };

        $this->capsule = new Manager($connection);
        return $this->capsule;
    }

    private function createMySQLConnection(array $config): ConnectionInterface
    {
        return new MySQLConnection($config + [
            'host' => 'localhost',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ]);
    }

    private function createSQLiteConnection(array $config): ConnectionInterface
    {
        if (!isset($config['database'])) {
            $config['database'] = __DIR__ . '/database.sqlite';
        }
        
        return new SQLiteConnection($config);
    }

    public function getCapsule(): Manager
    {
        return $this->capsule;
    }

    public function createTables(object $migration): void
    {
        $migration->up();
    }
}