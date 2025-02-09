<?php

namespace TurkerExecutor\Tests\Repositories;

use TurkerExecutor\Model\Model;
use TurkerExecutor\Tests\Models\PlayerModel;
use TurkerExecutor\Repositories\RepositoryInterface;

class PlayerRepository implements RepositoryInterface
{
    public function find(int $id): ?PlayerModel
    {
        return PlayerModel::find($id);
    }

    public function findBy(array $criteria): Model|PlayerModel|null
    {
        $query = new PlayerModel();
        foreach ($criteria as $field => $value) {
            $query = $query->where($field, '=', $value);
        }
        return $query->first();
    }

    public function findAll(): array
    {
        return PlayerModel::all();
    }

    public function create(array $data): PlayerModel
    {
        return PlayerModel::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->update($data);
        }
        return false;
    }

    public function delete(int $id): bool
    {
        $model = $this->find($id);
        if ($model) {
            return $model->delete();
        }
        return false;
    }
} 