<?php

namespace TurkerExecutor\Schema\Grammar;

abstract class Grammar
{
    abstract public function getIncrementsType(): string;
    abstract public function getBooleanType(): string;
    abstract public function getIntegerType(): string;
    abstract public function getTimestampType(): string;
    abstract public function getTableSuffix(): string;
    abstract public function wrapDefaultTimestamp(string $value): string;
} 