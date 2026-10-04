---
title: Reading titles
order: 4
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
| `Artist « Song » (2010)`, `“Song”` | any quotes |
| `Interview A / B (2024)`, `Interview et portrait A (1993)` | an interview, its artists |
| `Taratata Extra : Artist "Song" (2024)` | a bonus, with its song |
| `Artist au piano avec … (2024)` filed under Live | **not recognised**: no song in quotes - it keeps the artists it was filed under and is listed in the back office |

The parser's tests run on the show's own titles (`tests/fixtures/titles.json`).
