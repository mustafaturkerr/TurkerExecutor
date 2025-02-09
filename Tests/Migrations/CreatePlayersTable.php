<?php

namespace TurkerExecutor\Tests\Migrations;

use TurkerExecutor\Migrations\Migration;

class CreatePlayersTable extends Migration
{
    public function up()
    {
        // Önce tabloyu sil
        $this->schema->drop('players');

        // Sonra yeniden oluştur
        $this->schema->create('players', function ($table) {
            $table->id();
            $table->string('player')->unique();
            $table->integer('money')->default(0);
            $table->boolean('active')->default(true);
            $table->integer('team_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->drop('players');
    }
} 