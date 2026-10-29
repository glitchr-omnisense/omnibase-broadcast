---
title: The model
order: 20
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

`Suggest\BroadcastAffinity` implements omnibase/video's `AffinityInterface`
([Pages and Twig](pages.md#suggestions)).

## Fields and methods

`Episode`: `number` is unique within its programme (`broadcast_episode_number`
on programme and number); `airedAt` is a date; `image` is an address, not a
stored file; `full` is the whole show as one omnibase/video `Video` (SET NULL
when the film goes); `origin` is free text, where the episode came from.
`getYear()`, `getByKind($kind)` (its performances of one kind, in order),
`getPerformers()` (everyone on it, once each).

`Performance`: one to one with its `Video` (deleted with it), a `position` in
its episode, a `kind` among `Performance::KINDS` (`live`, `interview`,
`full`, `bonus`; indexed), its `song` (SET NULL), `cover`, its performers
(table `broadcast_performance_performer`), `rawTitle` (the title as it was
written) and `recognised` (whether the parser read it). `getBilling()` is the
performers' names joined by ` / `; `isLive()`; `isDuet()`, a live with two
performers or more; `isCover()`, the flag set when the song is somebody
else's.

`Song`: `title`, `originalArtist` (who made it known, or null), a unique
`slug` (190 characters).

`Profile`: one to one with a `Performer` (deleted with it), a unique `slug`,
`biography`, `image` (an address), `looked` (whether someone looked the
artist up already). `getName()` is the performer's.

`Programme`: `slug` (unique, 80), `name`, `description`, the omnibase/video
`Channel` its films are on (SET NULL).

## Catalogue in detail

`Catalogue::slug($text)` is the French ASCII slug, lower case, `sans-nom` for
nothing. `artist($name)` looks for a profile of that slug, else makes a
`Performer` of that name and its `Profile`, both persisted (not flushed).
`song($title, $originalArtist)` tries the slug `<title>-<original artist>`,
then `<title>`: a song found with no original artist takes the one given; a
song whose original artist differs is another song, made with the slug
`<title>-<original artist>` when `<title>` is taken. What it finds or makes
is remembered until `reset()`, to call after an `EntityManager::clear()`.

## Repositories in detail

The queries of performances read only the films that are online: published
and not taken down.

| Repository | Method | Returns |
|---|---|---|
| `EpisodeRepository` | `findOneByNumber($number)` | an episode by number |
| | `findLatest($limit = 12, $offset = 0)` | the last aired first |
| | `findByYear($year)`, `countByYear()` | a year's episodes; year => count, newest year first |
| | `findAnniversary($years)` | the episodes aired that many years ago within three days of today, or the closest one of that year |
| `PerformanceRepository` | `findForPerformer($performer, $kinds = ['live'], $limit = 200)` | an artist's performances of those kinds, the latest aired first |
| | `findForSong($song)` | every version of a song |
| | `findCovers($limit)`, `findDuets($limit)` | the most seen first |
| | `findUnrecognised($limit)`, `countUnrecognised()` | the titles not read |
| | `findOneForVideo($video)` | what a film is |
| | `findGroups($videoIds)` | the artist and episode groups of films, for the suggestions |
| `SongRepository` | `findWithSeveralVersions($limit = 24)` | the songs played more than once |
| `ProgrammeRepository` | `findCurrent(?string $slug)` | the programme of that slug, else the first one |
| `ProfileRepository` | `findAllByName()`, `findFor($performers)` | every profile by name; performer id => profile |

> [!WARNING]
> Known defect: `EpisodeRepository::findOneByNumber()` looks the number up in
> every programme, though it is unique only within one: with two programmes,
> `/emission/{number}` shows whichever comes first. `broadcast.programme`,
> which names the site's programme, is read by nothing in the bundle
> (`ProgrammeRepository::findCurrent()` takes the slug from its caller).
