<?php

namespace TurkerExecutor\Exceptions;

use Exception;

class ModelException extends Exception
{
    public static function notFound(string $model, int $id): self
    {
        return new self("{$model} bulunamadı (ID: {$id})");
    }

    public static function invalidRelation(string $model, string $relation): self
    {
        return new self("{$model} için geçersiz ilişki: {$relation}");
    }

    public static function updateFailed(string $model, int $id): self
    {
        return new self("{$model} güncellenemedi (ID: {$id})");
    }

    public static function deleteFailed(string $model, int $id): self
    {
        return new self("{$model} silinemedi (ID: {$id})");
    }
} 