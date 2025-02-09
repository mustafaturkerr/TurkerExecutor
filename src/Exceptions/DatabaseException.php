<?php

namespace TurkerExecutor\Exceptions;

use Exception;

class DatabaseException extends Exception
{
    public static function connectionFailed(string $message): self
    {
        return new self("Veritabanı bağlantısı başarısız: {$message}");
    }

    public static function transactionFailed(string $message): self
    {
        return new self("Transaction hatası: {$message}");
    }

    public static function queryFailed(string $message): self
    {
        return new self("Sorgu hatası: {$message}");
    }
} 