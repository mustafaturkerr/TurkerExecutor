<?php

namespace TurkerExecutor\Schema\Grammar;

class SQLiteGrammar extends Grammar
{
    public function getIncrementsType(): string
    {
        return 'INTEGER PRIMARY KEY AUTOINCREMENT';
    }

    public function getBooleanType(): string
    {
        return 'BOOLEAN';
    }

    public function getIntegerType(): string
    {
        return 'INTEGER';
    }

    public function getTimestampType(): string
    {
        return 'TIMESTAMP';
    }

    public function getTableSuffix(): string
    {
        return '';
    }

    public function wrapDefaultTimestamp(string $value): string
    {
        return "'" . $value . "'";
    }
} 