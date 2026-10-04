---
title: Installation
order: 2
---

# Installation

omnibase/video is installed first (its [installation](https://github.com/glitchr-studio/omnibase-video/blob/main/docs/installation.md)).

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
