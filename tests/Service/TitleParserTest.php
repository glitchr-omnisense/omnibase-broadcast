<?php

namespace Base\Broadcast\Tests\Service;

use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Service\TitleParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** The titles are the show's own, as its pages write them (tests/fixtures/titles.json). */
class TitleParserTest extends TestCase
{
    public static function titles(): iterable
    {
        foreach (json_decode((string) file_get_contents(__DIR__.'/../fixtures/titles.json'), true, 512, \JSON_THROW_ON_ERROR) as $case) {
            yield $case['title'] => [$case];
        }
    }

    /** @dataProvider titles */
    #[DataProvider('titles')]
    public function testARealTitleIsRead(array $case): void
    {
        $parsed = (new TitleParser())->parse($case['title'], $case['section'], $case['filed']);

        self::assertSame($case['kind'] ?? Performance::LIVE, $parsed->kind);
        self::assertSame($case['artists'], $parsed->artists);
        self::assertSame($case['song'], $parsed->song);
        self::assertSame($case['original'], $parsed->originalArtist);
        self::assertSame($case['year'], $parsed->year);
        self::assertSame($case['cover'], $parsed->isCover());
        self::assertSame($case['duet'], $parsed->isDuet());
        self::assertSame($case['recognised'] ?? true, $parsed->recognised);
        self::assertSame($case['notes'] ?? [], $parsed->notes);
        self::assertSame($case['title'], $parsed->raw);
    }

    public function testCurlyAndFrenchQuotesAreQuotes(): void
    {
        $parsed = (new TitleParser())->parse('Zaz « Je Veux » (2010)', 'Live');
        self::assertSame(['Zaz'], $parsed->artists);
        self::assertSame('Je Veux', $parsed->song);
        self::assertSame('Je Veux', (new TitleParser())->parse('Zaz “Je Veux” (2010)', 'Live')->song);
    }

    public function testAnArtistSingingTheirOwnSongNamedInBracketsIsNoCover(): void
    {
        $parsed = (new TitleParser())->parse('Lio / Hoshi "Amoureuse Solo" (Lio) (2026)', 'Live');
        self::assertSame('Lio', $parsed->originalArtist);
        self::assertFalse($parsed->isCover());
    }

    public function testWithoutASectionTheTitleSaysTheKind(): void
    {
        self::assertSame(Performance::INTERVIEW, TitleParser::kind(null, 'Interview Santa (2024)'));
        self::assertSame(Performance::BONUS, TitleParser::kind(null, 'Taratata Extra : Bobbie "Losing You" (2024)'));
        self::assertSame(Performance::LIVE, TitleParser::kind(null, 'Santa "Recommence-Moi" (2024)'));
        self::assertSame(Performance::BONUS, TitleParser::kind('Backstage'));
    }
}
