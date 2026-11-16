<?php

namespace Base\Broadcast\Repository;

use Base\Broadcast\Entity\Episode;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Episode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Episode|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Episode[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Episode::class);
    }

    /** The episode of that number in a programme: a number is unique within one programme only. Null: in any programme (the first found). */
    public function findOneByNumber(int $number, ?\Base\Broadcast\Entity\Programme $programme = null): ?Episode
    {
        return $this->findOneBy(['number' => $number] + (null !== $programme ? ['programme' => $programme] : []));
    }

    /** @return list<Episode> the last aired first */
    public function findLatest(int $limit = 12, int $offset = 0): array
    {
        return $this->findBy([], ['airedAt' => 'DESC', 'number' => 'DESC'], $limit, $offset);
    }

    /** @return list<Episode> */
    public function findByYear(int $year): array
    {
        // The bounds are bound with the column's type (date), as Types::DATE_MUTABLE bound them.
        return $this->findByAiredAtGreaterEqualToAndAiredAtLowerEqualTo(new \DateTime("$year-01-01"), new \DateTime("$year-12-31"), [], ['airedAt' => 'ASC', 'number' => 'ASC'])->getResult();
    }

    /** @return array<int, int> year => episodes, the latest year first. */
    public function countByYear(): array
    {
        // Every aired episode read (a programme's are a few hundred), the years counted here.
        $years = [];
        foreach ($this->findByAiredAtNotNull()->getResult() as $episode) {
            $year = (int) $episode->getAiredAt()->format('Y');
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
        $found = $this->findByAiredAtGreaterEqualToAndAiredAtLowerEqualTo((clone $then)->modify("-$days days"), (clone $then)->modify("+$days days"), [], ['airedAt' => 'ASC'])->getResult();
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
