---
title: Reading titles
order: 30
---

# Reading titles

```php
$parsed = $parser->parse('Jean-Louis Aubert / Santa "Diego, Libre Dans Sa Tête" (France Gall) (2024)', 'Live', ['Jean-Louis Aubert', 'Santa']);
$parsed->artists;         // ['Jean-Louis Aubert', 'Santa'] - the billing's order
$parsed->song;            // 'Diego, Libre Dans Sa Tête'
$parsed->originalArtist;  // 'France Gall'
$parsed->year;            // 2024
$parsed->isCover();       // true: France Gall is not on stage
$parsed->isDuet();        // true
$parsed->recognised;      // true
```

The second argument is the section the film is filed under (it says its
kind: `Live`, `Interview`, `Intégrale`, anything else is a bonus; without
it the title says); the third the artists it is filed under, used when the
title does not name them.

| Title | Read as |
|---|---|
| `Artist "Song" (1993)` | a live, its song, its year |
| `A / B "Song" (Original) (2024)` | a duet, a cover |
| `Artist "(Sittin' On) The Dock Of The Bay" (Otis Redding) (2024)` | brackets inside the title stay in it |
| `Artist "Ta Main" / "Toi + Moi" (2026)` | a medley: one song, `Ta Main / Toi + Moi` |
| `Artist "Song" (Inédit à la TV) (2026)` | a note, not an original artist (`notes`) |
| `Artist "Song" (1993` | a bracket left open |
| `A / B "Caravane (2025)` | a quote left open: closed before the brackets |
| `Artist « Song » (2010)`, `“Song”` | any quotes |
| `Interview A / B (2024)`, `Interview et portrait A (1993)` | an interview, its artists |
| `Taratata Extra : Artist "Song" (2024)` | a bonus, with its song |
| `Artist au piano avec … (2024)` filed under Live | **not recognised**: no song in quotes - it keeps the artists it was filed under and is listed in the back office |

The parser's tests run on the show's own titles (`tests/fixtures/titles.json`).

## The kind

`TitleParser::kind($section, $title)` reads the section first: `Live` is a
live, `Interview` an interview, `Intégrale` (or `Integrale`, `Full`) the whole
show, any other section a bonus. Without a section, the title: one that opens
with `Interview` is an interview, `Taratata N°` or `Intégrale` the whole show,
`Taratata Extra`, `Teaser`, `Bande Annonce`, `Les coulisses` or `Répétitions`
a bonus, anything else a live. Only a live needs a song to be recognised.

`TitleParser::artists('A / B')` splits a billing on ` / `, in the order
written, each name once.

## What the parser returns

`Base\Broadcast\Model\ParsedTitle`, read-only: `raw` (the title as given),
`kind`, `artists`, `song`, `originalArtist`, `year`, `notes` (the brackets
that describe the take rather than who made the song known: those opening
with `inédit`, `version`, `acoustique`, `live`, `medley`, `extrait`, `remix`,
`piano`, `bonus`, `en duo` or `avec`) and `recognised`;
`isLive()`, `isCover()` (an original artist who is not among the artists on
stage), `isDuet()` (a live with two artists or more).

## An example

```php
use Base\Broadcast\Service\TitleParser;

$parser = new TitleParser();
foreach ([
    ['Jean-Louis Aubert / Santa "Diego, Libre Dans Sa Tête" (France Gall) (2024)', 'Live', []],
    ['Zaz "Ta Main" / "Toi + Moi" (Inédit à la TV) (2026)', 'Live', []],
    ['Interview et portrait Alain Bashung (1993)', null, []],
    ['Feu! Chatterton au piano avec Pomme (2024)', 'Live', ['Feu! Chatterton']],
] as [$title, $section, $filed]) {
    $p = $parser->parse($title, $section, $filed);
    printf("%s\n  kind=%s artists=%s song=%s original=%s year=%s notes=%s cover=%s duet=%s recognised=%s\n",
        $title, $p->kind, json_encode($p->artists, JSON_UNESCAPED_UNICODE), var_export($p->song, true), var_export($p->originalArtist, true),
        var_export($p->year, true), json_encode($p->notes, JSON_UNESCAPED_UNICODE), var_export($p->isCover(), true), var_export($p->isDuet(), true), var_export($p->recognised, true));
}
```

Its output, run in omnisense/omnibase-harness:

```text
Jean-Louis Aubert / Santa "Diego, Libre Dans Sa Tête" (France Gall) (2024)
  kind=live artists=["Jean-Louis Aubert","Santa"] song='Diego, Libre Dans Sa Tête' original='France Gall' year=2024 notes=[] cover=true duet=true recognised=true
Zaz "Ta Main" / "Toi + Moi" (Inédit à la TV) (2026)
  kind=live artists=["Zaz"] song='Ta Main / Toi + Moi' original=NULL year=2026 notes=["Inédit à la TV"] cover=false duet=false recognised=true
Interview et portrait Alain Bashung (1993)
  kind=interview artists=["Alain Bashung"] song=NULL original=NULL year=1993 notes=[] cover=false duet=false recognised=true
Feu! Chatterton au piano avec Pomme (2024)
  kind=live artists=["Feu! Chatterton"] song=NULL original=NULL year=2024 notes=[] cover=false duet=false recognised=false
```

The last title has no song in quotes: it keeps the artist it was filed under
and is not recognised.

## From a title to a performance

A site's importer, for each film:

```php
$parsed = $parser->parse($video->getTitle(), $section, $filedUnder);
$performance = new Performance($episode, $video, $parsed->kind, $position);
$performance->setRawTitle($parsed->raw)->setRecognised($parsed->recognised);
foreach ($parsed->artists as $name) {
    $performance->addPerformer($catalogue->artist($name)->getPerformer());
}
if ($parsed->song) {
    $performance->setSong($catalogue->song($parsed->song, $parsed->originalArtist))
        ->setCover($parsed->isCover());
}
```
