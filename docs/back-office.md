---
title: Back office
order: 36
---

# Back office

```php
yield MenuItem::block('broadcast_unrecognised', 'Import : titres à reprendre', 'fa-solid fa-microphone-lines')->setSize(2);
MenuItem::linkToCrud(\Base\Broadcast\Entity\Episode::class, 'Émissions', 'fa-solid fa-tv');
MenuItem::linkToCrud(\Base\Broadcast\Entity\Performance::class, 'Passages', 'fa-solid fa-microphone-lines');
MenuItem::linkToCrud(\Base\Broadcast\Entity\Profile::class, 'Artistes', 'fa-solid fa-user-large');
MenuItem::linkToCrud(\Base\Broadcast\Entity\Song::class, 'Chansons', 'fa-solid fa-music');
MenuItem::linkToCrud(\Base\Broadcast\Entity\Programme::class, 'Émission (fiche)', 'fa-solid fa-tower-broadcast');
```

Written by `ROLE_ADMIN`. The tile counts the lives whose title was not
understood and links to them (`?filters[recognised]=0`): an administrator
gives each its song and marks it recognised.

## The screens

| Screen | Fields | Writes |
|---|---|---|
| `EpisodeCrudController` | number, aired on, title, programme, picture (an address), origin | create, edit, delete: `ROLE_ADMIN` |
| `PerformanceCrudController` | the title as written and the episode (read only), kind, position, recognised, song, cover; the performers on the index | edit, delete: `ROLE_ADMIN`; no "new": a performance comes from the import |
| `ProfileCrudController` | the performer (read only), slug, picture (an address), biography | edit, delete: `ROLE_ADMIN`; no "new": a profile comes with its artist |
| `SongCrudController` | title, original artist, slug | create, edit, delete: `ROLE_ADMIN` |
| `ProgrammeCrudController` | name, slug, channel, description | create, edit, delete: `ROLE_ADMIN` |

The performances screen filters on recognised, kind, cover and episode. The
screens grant their writes to `ROLE_ADMIN` with EasyAdmin's action
permissions rather than omnibase/admin's `#[OpenToAdmins]`.

## The tile

`Base\Broadcast\Admin\Widget\BroadcastWidgetType` is the widget
`broadcast_unrecognised` (`@Broadcast/admin/widget/unrecognised.html.twig`):
the number of titles not understood, the six latest, and the number of
episodes.

## A known limit

A performance's artists are shown, not edited, on its screen:
omnibase/admin's association widget cannot list omnibase/music's
`Performer` (its `kind` is a PHP enum the widget turns into a string). The
artists of a performance come from its title; to change them, correct the
title's reading in code or the performers in the database until the widget
takes enums.
