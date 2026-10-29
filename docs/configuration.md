---
title: "Configuration reference"
order: 40
---

# Configuration reference

## The `broadcast` tree

Dumped with Symfony's `YamlReferenceDumper` from
`Base\Broadcast\DependencyInjection\BroadcastConfiguration`:

```yaml
broadcast:

    # The slug of the site's programme (a site is one show's); null: the first one.
    programme:            null

    # "Il y a trente ans cette semaine": how many years back the home page looks.
    anniversary:          30
```

| Key | Default | Parameter | Read by |
|---|---|---|---|
| `programme` | null | `%broadcast.programme%` | nothing in the bundle; a site passes it to `ProgrammeRepository::findCurrent()` |
| `anniversary` | 30 | `%broadcast.anniversary%` | a site's home page, for `EpisodeRepository::findAnniversary()` |

A home page reads them as a controller argument:

```php
public function index(
    EpisodeRepository $episodes,
    #[Autowire('%broadcast.anniversary%')] int $anniversary,
): Response {
    return $this->render('home.html.twig', ['anniversary' => $episodes->findAnniversary($anniversary)]);
}
```

## Services

`config/services.php` autowires and autoconfigures the bundle's classes; the
admin screens and the tile are loaded with omnibase/admin.

| Service | Role |
|---|---|
| `Base\Broadcast\Service\TitleParser` | `parse()`, `kind()`, `artists()` |
| `Base\Broadcast\Service\Catalogue` | `artist()`, `song()`, `reset()`, `slug()` |
| `Base\Broadcast\Suggest\BroadcastAffinity` | tagged `video.affinity` by omnibase/video's interface |
| `Base\Broadcast\Twig\BroadcastExtension` | `broadcast_performance()`, `broadcast_profiles()` |
| the five repositories | [The model](model.md) |

## Translations

The domain `broadcast`, in English and French (ICU format). A site overrides
a message in its own `translations/broadcast+intl-icu.<locale>.yaml`.
