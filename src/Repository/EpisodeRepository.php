<?php

namespace Base\Broadcast\Repository;

use Base\Broadcast\Entity\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Episode> */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    /** The episode of that number in a programme: a number is unique within one programme only. Null: in any programme (the first found). */
    public function findOneByNumber(int $number, ?\Base\Broadcast\Entity\Programme $programme = null): ?Episode
    {
        return $this->findOneBy(['number' => $number] + (null !== $programme ? ['programme' => $programme] : []));
    }

    /** @return list<Episode> the last aired first */
    public function findLatest(int $limit = 12, int $offset = 0): array
    {
        return $this->createQueryBuilder('e')->orderBy('e.airedAt', 'DESC')->addOrderBy('e.number', 'DESC')
            ->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();
    }

    /** @return list<Episode> */
    public function findByYear(int $year): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.airedAt >= :from')->setParameter('from', new \DateTime("$year-01-01"), Types::DATE_MUTABLE)
            ->andWhere('e.airedAt <= :to')->setParameter('to', new \DateTime("$year-12-31"), Types::DATE_MUTABLE)
            ->orderBy('e.airedAt', 'ASC')->addOrderBy('e.number', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return array<int, int> year => episodes, the latest year first */
    public function countByYear(): array
    {
        $years = [];
        foreach ($this->createQueryBuilder('e')->select('e.airedAt AS at')->andWhere('e.airedAt IS NOT NULL')->getQuery()->getArrayResult() as $row) {
            $year = (int) $row['at']->format('Y');
            $years[$year] = ($years[$year] ?? 0) + 1;
        }
        krsort($years);

        return $years;
    }

    /**
     * "Il y a trente ans cette semaine": the episodes aired $years years ago,
     * within three days of today's date - or, when none was, the closest one
     * of that year.
     *
     * @return list<Episode>
     */
    public function findAnniversary(int $years = 30, ?\DateTimeInterface $today = null, int $days = 3): array
    {
        $then = \DateTime::createFromInterface($today ?? new \DateTime())->modify("-$years years");
        $found = $this->createQueryBuilder('e')
            ->andWhere('e.airedAt >= :from')->setParameter('from', (clone $then)->modify("-$days days"), Types::DATE_MUTABLE)
            ->andWhere('e.airedAt <= :to')->setParameter('to', (clone $then)->modify("+$days days"), Types::DATE_MUTABLE)
            ->orderBy('e.airedAt', 'ASC')->getQuery()->getResult();
        if ($found) {
            return $found;
        }
        $closest = null;
        foreach ($this->findByYear((int) $then->format('Y')) as $episode) {
            $gap = abs($episode->getAiredAt()->getTimestamp() - $then->getTimestamp());
            if (null === $closest || $gap < $closest[0]) {
                $closest = [$gap, $episode];
            }
        }

        return $closest ? [$closest[1]] : [];
    }
}
