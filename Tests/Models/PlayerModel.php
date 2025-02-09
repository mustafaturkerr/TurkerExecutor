<?php

namespace TurkerExecutor\Tests\Models;

use TurkerExecutor\Model\Model;
use TurkerExecutor\Query\QueryBuilder;
use TurkerExecutor\Relations\BelongsTo;

class PlayerModel extends Model
{
    protected string $table = 'players';

    /**
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamModel::class, 'team_id');
    }
    
    public function scopeActive(QueryBuilder $query): QueryBuilder
    {
        return $query->where('active', '=', true);
    }

    
    public static function player(string $value): QueryBuilder
    {
        return static::where('player', '=', $value);
    }
} 