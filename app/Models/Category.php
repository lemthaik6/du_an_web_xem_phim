<?php

namespace App\Models;

use App\Model;

/**
 * Model thao tác với bảng categories (thể loại).
 */
class Category extends Model
{
    public function all(): array
    {
        $qb = $this->connection->createQueryBuilder();
        return $qb->select('id', 'name', 'slug')
            ->from('categories')
            ->orderBy('name', 'ASC')
            ->fetchAllAssociative();
    }

    public function find(int $id): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $row = $qb->select('*')
            ->from('categories')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->fetchAssociative();

        return $row ?: null;
    }

    public function create(array $data): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('categories')
            ->values([
                'name' => ':name',
                'slug' => ':slug',
            ])
            ->setParameter('name', $data['name'])
            ->setParameter('slug', $data['slug'])
            ->executeQuery();
    }

    public function update(int $id, array $data): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update('categories')
            ->set('name', ':name')
            ->set('slug', ':slug')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->setParameter('name', $data['name'])
            ->setParameter('slug', $data['slug'])
            ->executeQuery();
    }

    public function delete(int $id): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->delete('categories')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery();
    }
}

