<?php

namespace TurkerExecutor\Repositories;

interface RepositoryInterface
{
    public function find(int $id);
    public function findBy(array $criteria);
    public function findAll();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
} 