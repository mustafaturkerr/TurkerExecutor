<?php

namespace TurkerExecutor\Tests\Migrations;

use TurkerExecutor\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    public function up()
    {
        $this->schema->drop('teams');

        $this->schema->create('teams', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('teams');
    }
} 