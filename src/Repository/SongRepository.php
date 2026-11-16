<?php

namespace Base\Broadcast\Repository;

use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Criterion;
use Base\Database\Repository\Finder\Aggregate;
use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Entity\Song;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Song|null find($id, $lockMode = null, $lockVersion = null)
 * @method Song|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Song[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class SongRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Song::class);
    }

    /**
     * The songs played more than once on stage, the most played first. A statement: GROUP BY,
     * HAVING COUNT() > 1, ordered by the count.
     *
     * @return list<array{song: Song, versions: int}>
     */
    public function findWithSeveralVersions(int $limit = 24): array
    {
        $rows = (new PerformanceRepository($this->finders()))->findValuesBy(
            ['song', 'versions' => Aggregate::count()],
            ['kind' => Performance::LIVE, Group::of(Parser::SEPARATOR_AND, Criterion::of('song', null, Parser::OPTION_NOT_NULL))],
            ['versions' => 'DESC'], $limit,
            having: [Group::of(Parser::SEPARATOR_AND, Criterion::of('versions', 1, Parser::OPTION_GREATER))],
        );
        $songs = [];
        foreach ($rows ? $this->findBy(['id' => array_column($rows, 'song')]) : [] as $song) {
            $songs[$song->getId()] = $song;
        }

        return array_values(array_filter(array_map(static fn (array $row) => isset($songs[$row['song']]) ? ['song' => $songs[$row['song']], 'versions' => (int) $row['versions']] : null, $rows)));
    }
}
