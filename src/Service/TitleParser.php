<?php

namespace Base\Broadcast\Service;

use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Model\ParsedTitle;

/**
 * A film's title as the show writes it, read:
 *
 *   Artiste A / Artiste B "Chanson" (Interprète d'origine) (Année)
 *
 * gives the artists in their billing order, the song, who made it known
 * (so: a cover, when they are not on stage), the year; two artists or more
 * are a duet. Also read: `Interview A / B (1993)`, `Taratata Extra : A
 * "Chanson" (2024)` (a bonus, with its song), a medley (`"Ta Main" / "Toi
 * + Moi"`), brackets inside a title (`"(Sittin' On) The Dock Of The Bay"`),
 * a note that is no artist (`(Inédit à la TV)`), a bracket left open
 * (`(1993`).
 *
 * The section the film is filed under (Live, Interview, Extra, Backstage,
 * Teaser...) says its kind. A live whose title has no song in quotes is
 * not recognised: it keeps the artists it was filed under and is listed in
 * the back office.
 */
class TitleParser
{
    /** Brackets that say something about the take, not who made the song known. */
    private const NOTE = '/^(in[ée]dit|version|acoustique|live|medley|extrait|remix|piano|bonus|en duo|avec )/iu';

    /** @param list<string> $filedUnder the artists the film is filed under, when its title does not name them */
    public function parse(string $title, ?string $section = null, array $filedUnder = []): ParsedTitle
    {
        $raw = trim($title);
        $text = trim(preg_replace('/\s+/u', ' ', strtr($raw, ['“' => '"', '”' => '"', '«' => '"', '»' => '"', '″' => '"'])) ?? $raw);
        $text = preg_replace('/"\s+|\s+"(?=\s*(?:\(|$))/u', '"', preg_replace('/(?<=^|\s)"\s+/u', '"', $text) ?? $text) ?? $text;
        $kind = self::kind($section, $text);
        $filedUnder = array_values(array_filter(array_map('trim', $filedUnder)));

        // The year: the last bracket, closed or not.
        $year = null;
        if (preg_match('/\s*\((\d{4})\)?\s*\.?$/u', $text, $m)) {
            $year = (int) $m[1];
            $text = trim(mb_substr($text, 0, -mb_strlen($m[0])));
        }

        if (Performance::INTERVIEW === $kind) {
            $names = preg_match('/^Interview(?: et portrait)?(?: de| d\')?\s+(.+)$/iu', $text, $m) ? self::artists($m[1]) : [];

            return new ParsedTitle($raw, $kind, $names ?: $filedUnder, null, null, $year);
        }
        if (Performance::FULL === $kind) {
            return new ParsedTitle($raw, $kind, $filedUnder, null, null, $year);
        }

        $extra = (bool) preg_match('/^Taratata Extra\s*:\s*/iu', $text, $m);
        if ($extra) {
            $text = mb_substr($text, mb_strlen($m[0]));
        }
        // Artists, then the song between the first quote and the last one, then what follows in brackets.
        if ((Performance::LIVE === $kind || $extra) && preg_match('/^(?<artists>[^"]+?)\s*"(?<song>.+)"\s*(?<rest>(?:\([^()]*\)\s*)*)$/u', $text, $m)) {
            $song = trim(preg_replace('/"\s*\/\s*"/u', ' / ', $m['song']) ?? $m['song']);
            $original = null;
            $notes = [];
            preg_match_all('/\(([^()]*)\)/u', $m['rest'], $brackets);
            foreach ($brackets[1] as $bracket) {
                $bracket = trim($bracket);
                if ('' === $bracket) {
                    continue;
                }
                if (null === $original && !preg_match(self::NOTE, $bracket)) {
                    $original = $bracket;
                } else {
                    $notes[] = $bracket;
                }
            }
            $artists = self::artists($m['artists']);
            if ($artists && '' !== $song) {
                return new ParsedTitle($raw, $kind, $artists, $song, $original, $year, $notes);
            }
        }

        return new ParsedTitle($raw, $kind, $filedUnder, null, null, $year, [], Performance::LIVE !== $kind);
    }

    public static function kind(?string $section, string $title = ''): string
    {
        $section = mb_strtolower(trim((string) $section));

        return match (true) {
            'live' === $section => Performance::LIVE,
            'interview' === $section => Performance::INTERVIEW,
            \in_array($section, ['intégrale', 'integrale', 'full'], true) => Performance::FULL,
            '' !== $section => Performance::BONUS,
            (bool) preg_match('/^Interview\b/iu', $title) => Performance::INTERVIEW,
            (bool) preg_match('/^(Taratata N°|Int[ée]grale)/iu', $title) => Performance::FULL,
            (bool) preg_match('/^(Taratata Extra|Teaser|Bande Annonce|Les coulisses|R[ée]p[ée]titions)/iu', $title) => Performance::BONUS,
            default => Performance::LIVE,
        };
    }

    /** @return list<string> "A / B" in the order written, once each */
    public static function artists(string $text): array
    {
        $names = array_map(fn ($name) => trim($name, " \t,;:"), preg_split('/\s+\/\s+/u', trim($text)) ?: []);

        return array_values(array_unique(array_filter($names, fn ($name) => '' !== $name)));
    }
}
