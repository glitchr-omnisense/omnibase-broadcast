---
title: Installation
order: 10
---

# Installation

omnibase/video is installed first (its [installation](https://github.com/glitchr-omnisense/omnibase-video/blob/main/docs/installation.md)).

```bash
composer require omnibase/broadcast:dev-main
```

```php
// config/bundles.php
Omnisong\Bridge\Symfony\OmnisongBundle::class => ['all' => true],   // omnibase/music's dependency
Base\Music\MusicBundle::class => ['all' => true],
Base\Broadcast\BroadcastBundle::class => ['all' => true],
```

```yaml
# config/routes.yaml
broadcast_controller:
    resource: "@BroadcastBundle/src/Controller/Client"
    type: attribute
    prefix: /
```

```yaml
# config/packages/broadcast.yaml
broadcast:
    programme: taratata   # the site's programme (its slug); null: the first one
    anniversary: 30       # "thirty years ago this week" looks that many years back
```

```twig
{# the layout, after omnibase/video's stylesheet #}
<link rel="stylesheet" href="{{ asset('bundles/broadcast/css/broadcast.css') }}">
```

Then a migration: `broadcast_programme`, `broadcast_episode`,
`broadcast_performance`, `broadcast_performance_performer`, `broadcast_song`,
`broadcast_profile` (and omnibase/music's tables).

A show's site usually closes omnibase/video's uploads and plays its films
where they are:

```yaml
# config/packages/video.yaml
video:
    upload: { enabled: false }
    remote: { hosts: [the-show.example] }
    suggest: { per_channel: 0 }      # one channel for the whole show
```

## Requirements

- PHP 8.2 or later;
- doctrine/orm 2.15 or 3;
- omnisense/omnibase 3.0 or 4.0, omnibridge/symfony 1.x, omnibase/admin;
- omnibase/video and omnibase/music (and omnisong, omnibase/music's
  dependency);
- Symfony 7 or 8 (config, dependency-injection, http-kernel, twig-bundle).

## Filling it

The bundle has no importer: a site writes the one its source needs (the show
the bundle was written for reads its channel's films and their sections),
with `TitleParser` to read each title and `Catalogue` to find or make its
artists and song ([Reading titles](titles.md)). The titles it cannot read are
kept, marked not recognised, for the back office to complete.
