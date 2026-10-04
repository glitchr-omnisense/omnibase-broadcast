<?php

namespace Base\Broadcast\Repository;

use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Entity\Song;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Song> */
class SongRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Song::class);
    }

    /**
     * The songs played more than once on stage, the most played first.
     *
     * @return list<array{song: Song, versions: int}>
     */
    public function findWithSeveralVersions(int $limit = 24): array
    {
        $rows = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(p.song) AS id, COUNT(p.id) AS n')->from(Performance::class, 'p')
            ->andWhere('p.song IS NOT NULL')->andWhere('p.kind = :live')->setParameter('live', Performance::LIVE)
            ->groupBy('p.song')->having('COUNT(p.id) > 1')->orderBy('n', 'DESC')->setMaxResults($limit)
            ->getQuery()->getArrayResult();
        $songs = [];
        foreach ($rows ? $this->findBy(['id' => array_column($rows, 'id')]) : [] as $song) {
            $songs[$song->getId()] = $song;
        }

        return array_values(array_filter(array_map(fn ($row) => isset($songs[$row['id']]) ? ['song' => $songs[$row['id']], 'versions' => (int) $row['n']] : null, $rows)));
    }
}
