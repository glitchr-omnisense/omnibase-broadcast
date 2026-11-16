<?php

namespace Base\Broadcast\Tests\Persistence;

use Base\Broadcast\Entity\Episode;
use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Entity\Programme;
use Base\Broadcast\Entity\Song;
use Base\Broadcast\Repository\EpisodeRepository;
use Base\Broadcast\Repository\PerformanceRepository;
use Base\Broadcast\Repository\SongRepository;
use Base\Entity\Thread;
use Base\Enum\ThreadState;
use Base\Enum\WorkflowState;
use Base\Music\Entity\Performer;
use Base\Video\Entity\Video;
use PHPUnit\Framework\Attributes\DataProvider;
use Base\Testing\Engines;
use PHPUnit\Framework\TestCase;

/** The broadcast's repositories give the same rows on Doctrine and on Eloquent: their statements and the finder around them. */
final class BroadcastStatementsTest extends TestCase
{
    private static Engines $engines;

    /** @return array<string, array{0: string}> */
    public static function backends(): array
    {
        return Engines::backends();
    }

    /** @var array<string, int> */
    private static array $ids = [];

    public static function setUpBeforeClass(): void
    {
        self::$engines = Engines::of([Programme::class, Episode::class, Performance::class, Song::class, Performer::class, Thread::class, Video::class]);
        $programme = new Programme('Programme');
        $first = (new Episode($programme, 1))->setAiredAt(new \DateTime('1996-03-02'));
        $second = (new Episode($programme, 2))->setAiredAt(new \DateTime('1997-05-04'));
        $third = (new Episode($programme, 3))->setAiredAt(new \DateTime('1997-06-01'));
        $song = new Song('Chanson', null, 'chanson');
        $other = new Song('Autre', null, 'autre');
        $alice = (new Performer())->setName("Alice");
        $bob = (new Performer())->setName("Bob");
        self::$engines->seed($programme, $first, $second, $third, $song, $other, $alice, $bob);
        // A film is a Thread (its constructor writes a translated title, which needs the translator): its rows written
        // here, and the performances' rows beside them.
        $film = static function (string $title, int $views, ?int $legacy = null, string $state = ThreadState::PUBLISH): int {
            $id = self::$engines->insert('thread', ['uuid' => uniqid('', true), 'slug' => strtolower($title), 'state' => $state, 'workflow' => WorkflowState::APPROVED,
                'createdAt' => '2026-01-01 00:00:00', 'updatedAt' => '2026-01-01 00:00:00', 'class' => self::$engines->discriminator(Video::class)]);
            self::$engines->insert('video', ['id' => $id, 'views' => $views, 'legacyViews' => $legacy]);

            return $id;
        };
        $performance = static function (Episode $episode, int $film, Song $song, bool $cover, Performer ...$performers): int {
            $id = self::$engines->insert('broadcast_performance', ['episode_id' => $episode->getId(), 'video_id' => $film, 'position' => 0, 'kind' => Performance::LIVE,
                'song_id' => $song->getId(), 'cover' => (int) $cover, 'recognised' => 1]);
            foreach ($performers as $performer) {
                self::$engines->insert('broadcast_performance_performer', ['broadcast_performance_id' => $id, 'music_performer_id' => $performer->getId()]);
            }

            return $id;
        };
        $filmA = $film('A', 10, 100);
        $filmD = $film('D', 5);
        $a = $performance($first, $filmA, $song, true, $alice, $bob);
        $b = $performance($second, $film('B', 50), $song, true, $alice);
        $c = $performance($third, $film('C', 500, null, ThreadState::DRAFT), $song, true, $alice, $bob);
        $d = $performance($third, $filmD, $other, false, $bob);
        self::$ids = ['a' => $a, 'b' => $b, 'c' => $c, 'd' => $d, 'filmA' => $filmA, 'filmD' => $filmD, 'alice' => (int) $alice->getId(), 'bob' => (int) $bob->getId(),
            'first' => (int) $first->getId(), 'third' => (int) $third->getId()];
    }

    /** @param list<Performance> $performances @return list<int> */
    private static function ids(array $performances): array
    {
        return array_map(static fn (Performance $p): int => (int) $p->getId(), $performances);
    }

    /** @dataProvider backends */
    #[DataProvider('backends')]
    public function testDuetsAndCovers(string $backend): void
    {
        $performances = new PerformanceRepository(self::$engines->finders($backend));
        $ids = $performances->duetIds();
        sort($ids);
        self::assertSame([self::$ids['a'], self::$ids['c']], $ids, 'two performers or more, whatever the film');
        self::assertSame([self::$ids['a']], self::ids($performances->findDuets()), 'online only (C is a draft)');
        self::assertSame([self::$ids['a'], self::$ids['b']], self::ids($performances->findCovers()), 'the most seen first: 10 + 100 imported over 50');
    }

    /** @dataProvider backends */
    #[DataProvider('backends')]
    public function testGroups(string $backend): void
    {
        $groups = (new PerformanceRepository(self::$engines->finders($backend)))->findGroups([self::$ids['filmA'], self::$ids['filmD']]);
        ksort($groups);
        foreach ($groups as &$group) {
            sort($group['performers']);
        }
        self::assertSame([
            self::$ids['filmA'] => ['episode' => self::$ids['first'], 'performers' => [self::$ids['alice'], self::$ids['bob']]],
            self::$ids['filmD'] => ['episode' => self::$ids['third'], 'performers' => [self::$ids['bob']]],
        ], $groups);
        self::assertSame([], (new PerformanceRepository(self::$engines->finders($backend)))->findGroups([]));
    }

    /** @dataProvider backends */
    #[DataProvider('backends')]
    public function testSongsWithSeveralVersions(string $backend): void
    {
        $several = (new SongRepository(self::$engines->finders($backend)))->findWithSeveralVersions();
        self::assertSame([['chanson', 3]], array_map(static fn (array $row): array => [$row['song']->getSlug(), $row['versions']], $several));
    }

    /** @dataProvider backends */
    #[DataProvider('backends')]
    public function testEpisodesByYear(string $backend): void
    {
        self::assertSame([1997 => 2, 1996 => 1], (new EpisodeRepository(self::$engines->finders($backend)))->countByYear());
    }
}
