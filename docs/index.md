---
title: omnibase/broadcast
order: 1
---

# omnibase/broadcast

omnibase/broadcast is a broadcast's site on omnibase: a programme, its
episodes, and for each film of an episode what it is - a song played live (by
whom, their own or a cover, a duet), an interview, the whole show, a bonus.
It was written for one music show and is no more general than that needed.
Its namespace is `Base\Broadcast\`.

## What it adds

- Five entities: `Programme`, `Episode`, `Performance`, `Song`, `Profile`
  ([The model](model.md)).
- `TitleParser`, which reads a film's title into its artists, song, original
  artist, year and kind, and `Catalogue`, which finds or makes the artists and
  songs a title names ([Reading titles](titles.md)).
- Eight pages: the episodes by year, an episode, the artists, an artist, the
  songs played more than once, a song, the covers, the duets
  ([Pages and Twig](pages.md)).
- Two Twig functions and the partials a film's page and a home page include.
- An affinity for omnibase/video's suggestions: same artist, same episode.
- Five back office screens and a tile counting the titles not understood
  ([Back office](back-office.md)).
- English and French messages (domain `broadcast`).

## What it builds on

- omnibase/video's films: `Video`, `Channel`, the player, comments, lists,
  suggestions;
- omnibase/music's `Performer` for the artists;
- omnisense/omnibase and omnibase/admin.

The bundle imports nothing by itself: a site reads its source (a channel's
films, a list of titles) and makes episodes and performances with
`TitleParser` and `Catalogue`.

## Pages

| Page | |
|---|---|
| [Installation](installation.md) | the bundle, its routes, its stylesheet, its configuration |
| [The model](model.md) | `Programme`, `Episode`, `Performance`, `Song`, `Profile`, `Catalogue` |
| [Reading titles](titles.md) | `TitleParser`: what it understands, what it lists for the back office |
| [Pages and Twig](pages.md) | the routes, the partials, the film page's block |
| [Back office](back-office.md) | the screens, the tile, a known limit |
| [Configuration reference](configuration.md) | `broadcast:` dumped, the services |
| [Testing](testing.md) | the suite, what it checks |
