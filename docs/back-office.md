---
title: Back office
order: 6
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

## A known limit

A performance's artists are shown, not edited, on its screen:
omnibase/admin's association widget cannot list omnibase/music's
`Performer` (its `kind` is a PHP enum the widget turns into a string). The
artists of a performance come from its title; to change them, correct the
title's reading in code or the performers in the database until the widget
takes enums.
