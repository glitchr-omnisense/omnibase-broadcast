<?php

namespace Base\Broadcast\Repository;

use Base\Database\Repository\Finder\Aggregate;
use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finder\Key;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Criterion;
use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Entity\Song;
use Base\Enum\ThreadState;
use Base\Music\Entity\Performer;
use Base\Video\Entity\Video;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Performance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Performance|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Performance[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class PerformanceRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Performance::class);
    }

    public function findOneForVideo(Video $video): ?Performance
    {
        return $this->findOneBy(['video' => $video]);
    }

    /**
     * The criteria of the performances whose film is online: the film's state and take-down through one join of it,
     * as `JOIN p.video v WHERE v.state = :published AND v.takenDownAt IS NULL` (the film and the episode are required:
     * the inner joins kept every row). The film and the episode were fetch-joined; they load lazily now.
     *
     * @return array<int|string, mixed>
     */
    private static function onlineCriteria(): array
    {
        return ['video.state' => ThreadState::PUBLISH, Group::of(Parser::SEPARATOR_AND, Criterion::of('video.takenDownAt', null, Parser::OPTION_NULL))];
    }


    /** @return list<Performance> an artist's, the latest first; $kinds: live, interview... */
    public function findForPerformer(Performer $performer, array $kinds = [Performance::LIVE], int $limit = 200): array
    {
        return $this->findBy(self::onlineCriteria() + ['performers' => $performer, 'kind' => $kinds], ['episode.airedAt' => 'DESC', 'position' => 'ASC'], $limit);
    }

    /** @return list<Performance> every version of a song played on stage, the first one first */
    public function findForSong(Song $song): array
    {
        return $this->findBy(self::onlineCriteria() + ['song' => Key::of($song)], ['episode.airedAt' => 'ASC', 'position' => 'ASC']);
    }

    /** @return list<Performance> the covers, the most seen first */
    public function findCovers(int $limit = 12): array
    {
        return self::mostSeen($this->findBy(self::onlineCriteria() + ['cover' => true, 'kind' => Performance::LIVE]), $limit);
    }

    /** @return list<Performance> the duets, the most seen first */
    public function findDuets(int $limit = 12): array
    {
        $ids = $this->duetIds();

        return $ids ? self::mostSeen($this->findBy(self::onlineCriteria() + ['id' => $ids]), $limit) : [];
    }

    /**
     * The lives played by two performers or more (at most 2 000): GROUP BY the performance over its performers.
     *
     * @return list<int>
     */
    public function duetIds(): array
    {
        $rows = $this->findValuesBy(['id', 'performers' => Aggregate::count('performers')], ['kind' => Performance::LIVE], null, 2000,
            having: [Group::of(Parser::SEPARATOR_AND, Criterion::of('performers', 1, Parser::OPTION_GREATER))]);

        return array_map(static fn (array $row): int => (int) $row['id'], $rows);
    }

    /**
     * The most seen first (the film's views and its imported ones), the first $limit: the online covers and duets
     * are a few hundred, sorted here rather than by an expression in the query.
     *
     * @param list<Performance> $performances
     *
     * @return list<Performance>
     */
    private static function mostSeen(array $performances, int $limit): array
    {
        usort($performances, static fn (Performance $a, Performance $b): int => $b->getVideo()->getTotalViews() <=> $a->getVideo()->getTotalViews());

        return \array_slice($performances, 0, $limit);
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
        // One row per film and performer (a film without performers once, its artist null).
        $groups = [];
        foreach ($this->findValuesBy(['film' => 'video', 'episode' => 'episode', 'artist' => 'performers.id'], ['video' => Key::of($videoIds)]) as $row) {
            $groups[(int) $row['film']]['episode'] = (int) $row['episode'];
            $groups[(int) $row['film']]['performers'] ??= [];
            if (null !== $row['artist']) {
                $groups[(int) $row['film']]['performers'][] = (int) $row['artist'];
            }
        }

        return $groups;
    }
}
