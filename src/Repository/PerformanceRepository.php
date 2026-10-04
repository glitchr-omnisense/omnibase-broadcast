<?php

namespace Base\Broadcast\Repository;

use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Entity\Song;
use Base\Enum\ThreadState;
use Base\Music\Entity\Performer;
use Base\Video\Entity\Video;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Performance> */
class PerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Performance::class);
    }

    public function findOneForVideo(Video $video): ?Performance
    {
        return $this->findOneBy(['video' => $video]);
    }

    /** Performances whose film is online. */
    private function online(): QueryBuilder
    {
        return $this->createQueryBuilder('p')->innerJoin('p.video', 'v')->addSelect('v')->innerJoin('p.episode', 'e')->addSelect('e')
            ->andWhere('v.state = :published')->setParameter('published', ThreadState::PUBLISH)
            ->andWhere('v.takenDownAt IS NULL');
    }

    /** @return list<Performance> an artist's, the latest first; $kinds: live, interview... */
    public function findForPerformer(Performer $performer, array $kinds = [Performance::LIVE], int $limit = 200): array
    {
        return $this->online()->innerJoin('p.performers', 'a')->andWhere('a = :performer')->setParameter('performer', $performer)
            ->andWhere('p.kind IN (:kinds)')->setParameter('kinds', $kinds)
            ->orderBy('e.airedAt', 'DESC')->addOrderBy('p.position', 'ASC')->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /** @return list<Performance> every version of a song played on stage, the first one first */
    public function findForSong(Song $song): array
    {
        return $this->online()->andWhere('p.song = :song')->setParameter('song', $song)
            ->orderBy('e.airedAt', 'ASC')->addOrderBy('p.position', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return list<Performance> the covers, the most seen first */
    public function findCovers(int $limit = 12): array
    {
        return $this->online()->andWhere('p.cover = true')->andWhere('p.kind = :live')->setParameter('live', Performance::LIVE)
            ->addSelect('(v.views + COALESCE(v.legacyViews, 0)) AS HIDDEN total')->orderBy('total', 'DESC')->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /** @return list<Performance> the duets, the most seen first */
    public function findDuets(int $limit = 12): array
    {
        $ids = array_column($this->createQueryBuilder('p')->select('p.id')->innerJoin('p.performers', 'a')
            ->andWhere('p.kind = :live')->setParameter('live', Performance::LIVE)
            ->groupBy('p.id')->having('COUNT(a.id) > 1')->setMaxResults(2000)
            ->getQuery()->getArrayResult(), 'id');
        if (!$ids) {
            return [];
        }

        return $this->online()->andWhere('p.id IN (:ids)')->setParameter('ids', $ids)
            ->addSelect('(v.views + COALESCE(v.legacyViews, 0)) AS HIDDEN total')->orderBy('total', 'DESC')->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /** @return list<Performance> the lives whose title was not understood */
    public function findUnrecognised(int $limit = 50): array
    {
        return $this->findBy(['recognised' => false], ['id' => 'DESC'], $limit);
    }

    public function countUnrecognised(): int
    {
        return $this->count(['recognised' => false]);
    }

    /**
     * For the suggestions: each film's artists and episode.
     *
     * @param list<int> $videoIds
     *
     * @return array<int, array{episode: int, performers: list<int>}>
     */
    public function findGroups(array $videoIds): array
    {
        if (!$videoIds) {
            return [];
        }
        $groups = [];
        foreach ($this->createQueryBuilder('p')->select('IDENTITY(p.video) AS film, IDENTITY(p.episode) AS episode, a.id AS artist')
            ->leftJoin('p.performers', 'a')->andWhere('p.video IN (:ids)')->setParameter('ids', $videoIds)
            ->getQuery()->getArrayResult() as $row) {
            $groups[(int) $row['film']]['episode'] = (int) $row['episode'];
            $groups[(int) $row['film']]['performers'] ??= [];
            if (null !== $row['artist']) {
                $groups[(int) $row['film']]['performers'][] = (int) $row['artist'];
            }
        }

        return $groups;
    }
}
