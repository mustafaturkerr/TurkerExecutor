<?php

namespace TurkerExecutor\Migrations;

use TurkerExecutor\Schema\Builder;
use TurkerExecutor\TurkerExecutor;

abstract class Migration
{
    protected Builder $schema;

    public function __construct()
    {
        $this->schema = TurkerExecutor::getInstance()->getCapsule()->schema();
    }

    abstract public function up();
    abstract public function down();
} 