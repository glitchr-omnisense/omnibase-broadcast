# Broadcast

A broadcast's site on [omnibase](https://github.com/glitchr-studio/omnibase):
a programme, its episodes, and for each film of an episode what it is - a
song played live (by whom, their own or a cover, a duet), an interview, the
whole show, a bonus. Written for one music show, no more general than it
needed.

- The films are [omnibase/video](https://github.com/glitchr-studio/omnibase-video)'s
  (`Video`, its player, comments, lists, suggestions).
- The artists are [omnibase/music](https://github.com/glitchr-studio/omnibase-music)'s
  `Performer`; a `Profile` gives one a page (address, story, picture).
- `Programme`, `Episode`, `Performance`, `Song` are this bundle's.
- `TitleParser` reads a film's title - `Artiste A / Artiste B "Chanson"
  (Interprète d'origine) (Année)` - into artists, song, cover, duet, year.
- Pages: the episodes by year, an episode (the whole show, the order of
  passage), the artists, an artist, a song and every version of it played
  on stage, the covers, the duets.
- The suggestions of omnibase/video learn "same artist" and "same episode"
  (`BroadcastAffinity`).
- Back office: episodes, performances (the titles not understood, to
  complete), songs, artists' pages, a dashboard tile.

```bash
composer require omnibase/broadcast:dev-main
```

Documentation: [docs/](docs/index.md). Tests: `vendor/bin/phpunit`.

Licence: MIT since 2026-10-09; earlier versions remain published under LGPL-3.0-or-later.
