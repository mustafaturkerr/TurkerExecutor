<?php

namespace TurkerExecutor\Schema\Grammar;

class MySqlGrammar extends Grammar
{
    public function getIncrementsType(): string
    {
        return 'INT AUTO_INCREMENT PRIMARY KEY';
    }

    public function getBooleanType(): string
    {
        return 'TINYINT(1)';
    }

    public function getIntegerType(): string
    {
        return 'INT';
    }

    public function getTimestampType(): string
    {
        return 'TIMESTAMP';
    }

    public function getTableSuffix(): string
    {
        return ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    }

    public function wrapDefaultTimestamp(string $value): string
    {
        return $value;
    }
} 