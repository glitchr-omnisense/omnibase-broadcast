# Changelog

## [Unreleased]

## [1.0.2] - 2026-11-14

### Changed

- Accepts omnisense/omnibase 6.0: `omnisense/omnibase` ^5.1 || ^6.0, `omnibridge/symfony` ^1.1 || ^2.0. The
  same code runs on both lines: it names no former name and no entry point 6.0 removed.

## [1.0.1] - 2026-11-14

### Changed

- The classes omnisense/omnibase 5.0 moved to omnibridge/symfony named by their new names:
  `Omnibridge\Symfony\Omnibase\Bundle\{AbstractBaseBundle, AbstractBaseExtension, AbstractBaseConfiguration}`
  in place of `Base\Bundle\*`, former names the core keeps only as aliases until 6.0. No behaviour change.

## [1.0.0] - 2026-11-13

### Changed

- Installs with the default minimum-stability: stable requirements (`omnisense/omnibase` ^5.1, `omnibridge/symfony` ^1.1, `omnibase/admin`, `omnibase/music` and
  `omnibase/video` ^1.0), a branch alias,
  no `minimum-stability: dev` left to the sites.
- CI: glitchr-studio/ci's package workflow (the bundle installed alone, composer validate, PHPUnit,
  PHPStan against its baseline, a consumer install without require-dev) and its release workflow.
- `doctrine/doctrine-bundle` required: the repositories extend its `ServiceEntityRepository`.
- `TitleParser` reads the show's own name, whatever it is (one or two words before `N°<number>`
  or `Extra :`), instead of one show's; neutral example data in the docs and tests.

### Changed
- Accepts omnisense/omnibase 5.0 (`3.0.*-dev || 4.0.*-dev || 5.0.*-dev`; plan "omnibase agnostic", step S): the bundle runs
  unchanged on it, `Base\Bundle\AbstractBaseBundle` and the other former names it uses being aliases of
  omnibridge/symfony's classes there. The sites on 4.x resolve as before.

### Fixed
- `/emission/{number}` shows the episode of that number in the site's programme
  (`broadcast.programme`, else the first): `EpisodeRepository::findOneByNumber()` looked the number
  up in every programme, though it is unique within one only, and `broadcast.programme` was read
  by nothing. `findOneByNumber()` takes the programme as a second, optional argument.
