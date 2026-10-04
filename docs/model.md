---
title: The model
order: 3
---

# The model

| Entity | Table | |
|---|---|---|
| `Programme` | `broadcast_programme` | the show: name, slug, description, the `Channel` its films are on |
| `Episode` | `broadcast_episode` | one broadcast: number (unique in its programme), `airedAt`, picture (an address), `full` (the whole show as one `Video`), `origin`, its performances in order |
| `Performance` | `broadcast_performance` | what one film is: its episode, its `Video` (one to one), its position, its kind (`live`, `interview`, `full`, `bonus`), its `Song`, `cover`, its performers (omnibase/music's `Performer`), the title as it was written, whether it was understood |
| `Song` | `broadcast_song` | a title and who made it known; its page gathers every version |
| `Profile` | `broadcast_profile` | an artist's page: the `Performer`, a slug, a biography, a picture |

`Performance::isDuet()`: a live with two performers or more.
`Performance::isCover()`: its song is somebody else's.

## Catalogue

```php
$profile = $catalogue->artist('Jean-Louis Aubert');   // the Performer and its Profile, found by slug or made
$song = $catalogue->song('Diego, Libre Dans Sa Tête', 'France Gall');
```

One `Performer` for a name however often it comes; one `Song` for a title
and its original artist (the same title by another original artist is
another song; a version that does not say whose it is joins the one that
exists). What it makes in one run is remembered until `reset()`.

## Repositories

- `EpisodeRepository`: `findLatest()`, `findByYear()`, `countByYear()`,
  `findAnniversary($years)` - the episodes aired that many years ago within
  three days of today, or the closest one of that year.
- `PerformanceRepository`: `findForPerformer()`, `findForSong()`,
  `findCovers()`, `findDuets()` (the most seen first), `findUnrecognised()`,
  `findOneForVideo()`.
- `SongRepository::findWithSeveralVersions()`.

## Suggestions

`Suggest\BroadcastAffinity` implements omnibase/video's `AffinityInterface`:
two films of one artist are as close as two films of one channel (3), two
films of one episode as close as two films of one list (2).
