<?php

namespace TurkerExecutor\Support;

class Str
{
    public static function classBasename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
} 