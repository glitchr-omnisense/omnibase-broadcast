<?php

namespace Base\Broadcast\Tests\Service;

use Base\Broadcast\Controller\Client\BroadcastController;
use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Repository\PerformanceRepository;
use Base\Broadcast\Service\Catalogue;
use Base\Broadcast\Suggest\BroadcastAffinity;
use PHPUnit\Framework\TestCase;

class AffinityTest extends TestCase
{
    public function testTheSameArtistWeighsAsAChannelTheSameEpisodeAsAList(): void
    {
        $performances = $this->createStub(PerformanceRepository::class);
        $performances->method('findGroups')->willReturn([
            10 => ['episode' => 584, 'performers' => [1, 2]],
            11 => ['episode' => 584, 'performers' => [2]],
            12 => ['episode' => 12, 'performers' => []],
        ]);
        $groups = (new BroadcastAffinity($performances))->groups([]);

        self::assertSame(['episode:584' => 2.0, 'artist:1' => 3.0, 'artist:2' => 3.0], $groups[10]);
        self::assertSame(['episode:584' => 2.0, 'artist:2' => 3.0], $groups[11]);
        self::assertSame(['episode:12' => 2.0], $groups[12]);
        self::assertSame(['episode:584' => 2.0, 'artist:2' => 3.0], array_intersect_key($groups[10], $groups[11]), 'what two films of one night by one artist share');
    }

    public function testYearsFallIntoDecadesTheLatestFirst(): void
    {
        self::assertSame([2020 => [2024, 2026], 1990 => [1993, 1999]], BroadcastController::decades([2026, 1999, 2024, 1993]));
    }

    public function testNamesAndTitlesBecomeAddresses(): void
    {
        self::assertSame('jean-louis-aubert', Catalogue::slug('Jean-Louis Aubert'));
        self::assertSame('noe-preszow', Catalogue::slug('Noé Preszow'));
        self::assertSame('dis-quand-reviendras-tu', Catalogue::slug('Dis Quand Reviendras-Tu ?'));
        self::assertSame('sans-nom', Catalogue::slug('???'));
        self::assertSame([Performance::LIVE, Performance::INTERVIEW, Performance::FULL, Performance::BONUS], Performance::KINDS);
    }
}
