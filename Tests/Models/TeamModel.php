<?php

namespace TurkerExecutor\Tests\Models;

use TurkerExecutor\Model\Model;

class TeamModel extends Model
{
    protected string $table = 'teams';
    
    public function players()
    {
        return $this->hasMany(PlayerModel::class, 'team_id');
    }
} 