---
title: Pages and Twig
order: 5
---

# Pages

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

# Twig

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
