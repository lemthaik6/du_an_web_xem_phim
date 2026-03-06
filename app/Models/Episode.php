<?php

namespace App\Models;

use App\Model;

/**
 * Model thao tác với bảng episodes, phục vụ cả frontend và admin.
 */
class Episode extends Model
{
    public function listByMovie(int $movieId): array
    {
        $qb = $this->connection->createQueryBuilder();
        return $qb->select('id', 'movie_id', 'episode_number', 'title', 'video_url')
            ->from('episodes')
            ->where('movie_id = :mid')
            ->setParameter('mid', $movieId)
            ->orderBy('episode_number', 'ASC')
            ->fetchAllAssociative();
    }

    public function find(int $id): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $row = $qb->select('*')
            ->from('episodes')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->fetchAssociative();

        return $row ?: null;
    }

    public function create(array $data): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('episodes')
            ->values([
                'movie_id'       => ':movie_id',
                'episode_number' => ':episode_number',
                'title'          => ':title',
                'video_url'      => ':video_url',
            ])
            ->setParameter('movie_id', $data['movie_id'])
            ->setParameter('episode_number', $data['episode_number'])
            ->setParameter('title', $data['title'])
            ->setParameter('video_url', $data['video_url'])
            ->executeQuery();
    }

    public function update(int $id, array $data): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update('episodes')
            ->set('episode_number', ':episode_number')
            ->set('title', ':title')
            ->set('video_url', ':video_url')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->setParameter('episode_number', $data['episode_number'])
            ->setParameter('title', $data['title'])
            ->setParameter('video_url', $data['video_url'])
            ->executeQuery();
    }

    public function delete(int $id): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->delete('episodes')
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery();
    }
}

