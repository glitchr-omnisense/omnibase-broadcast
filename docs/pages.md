---
title: Pages and Twig
order: 31
---

# Pages and Twig

## Routes

| Route | Path | |
|---|---|---|
| `broadcast_episodes` | `/emissions?annee=` | a year's episodes; the years by decade |
| `broadcast_episode` | `/emission/{number}` | the whole show, the order of passage, interviews, bonus |
| `broadcast_artists` | `/artistes` | every artist, by letter |
| `broadcast_artist` | `/artiste/{slug}` | their story, their lives (the latest first), interviews and bonus |
| `broadcast_songs` | `/chansons` | the songs played more than once |
| `broadcast_song` | `/chanson/{slug}` | every version of a song played on stage |
| `broadcast_covers`, `broadcast_duets` | `/reprises`, `/duos` | the most seen first |

They extend `layout1.html.twig` and use omnibase/video's custom properties.
An unknown episode, artist or song is a 404.

## What each page shows

- `/emissions`: the episodes of `?annee=` (the latest year with episodes by
  default), the years grouped by decade (`BroadcastController::decades()`,
  newest decade first, its years in order) with their counts.
- `/emission/{number}`: the episode, its whole show, its lives in the order of
  passage, its interviews, its bonus, and the artists' profiles for the links.
- `/artistes`: every profile by name, under its first letter (accents
  removed; `#` for a name that does not start with a letter).
- `/artiste/{slug}`: the profile, its lives (the latest aired first), its
  interviews and bonus.
- `/chansons`: up to 60 songs played more than once.
- `/chanson/{slug}`: every version of the song played on stage, with the
  profiles of everyone who sang it.
- `/reprises`, `/duos`: up to 60 covers or duets, the most seen first
  (`@Broadcast/client/list.html.twig`).

Only the films that are online (published, not taken down) are listed.

## Twig

```twig
{% include '@Broadcast/client/_performance.html.twig' with {performance: p, profiles: profiles, show_episode: true} only %}
{% include '@Broadcast/client/_episode.html.twig' with {episode: e} only %}
{% set performance = broadcast_performance(video) %}      {# what a film is, or null #}
{% set profiles = broadcast_profiles(performers) %}      {# performer id => Profile, for the links #}
```

On a film's page, omnibase/video leaves a block for it - the application
overrides the template once:

```twig
{# templates/bundles/VideoBundle/client/watch.html.twig #}
{% extends '@!Video/client/watch.html.twig' %}
{% block video_extra %}{% include '@Broadcast/client/_info.html.twig' with {video: video} %}{% endblock %}
```

A home page asks the repositories: `EpisodeRepository::findLatest(1)`,
`findAnniversary(30)`, `countByYear()` with
`BroadcastController::decades()`, `PerformanceRepository::findCovers()`,
`findDuets()`.

| Function | Returns |
|---|---|
| `broadcast_performance(video)` | the `Performance` a film is, or null |
| `broadcast_profiles(performers)` | performer id => `Profile`, for the links |

| Partial | Variables |
|---|---|
| `@Broadcast/client/_performance.html.twig` | `performance`, `profiles`, `show_episode` |
| `@Broadcast/client/_episode.html.twig` | `episode` |
| `@Broadcast/client/_info.html.twig` | `video`: what the film is, under omnibase/video's player |

## Suggestions

`Base\Broadcast\Suggest\BroadcastAffinity` implements omnibase/video's
`AffinityInterface`: `groups($videos)` returns, for each film, its artists and
its episode as groups, weighed like omnibase/video's own (an artist as a
channel, 3; an episode as a list, 2). omnibase/video's suggestions then put
the films of the same artist and of the same episode next to the one being
watched.
