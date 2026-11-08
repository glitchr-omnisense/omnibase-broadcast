# Changelog

## [Unreleased]

### Changed
- Accepts omnisense/omnibase 5.0 (`3.0.*-dev || 4.0.*-dev || 5.0.*-dev`; plan "omnibase agnostic", step S): the bundle runs
  unchanged on it, `Base\Bundle\AbstractBaseBundle` and the other former names it uses being aliases of
  omnibridge/symfony's classes there. The sites on 4.x resolve as before.

### Fixed
- `/emission/{number}` shows the episode of that number in the site's programme
  (`broadcast.programme`, else the first): `EpisodeRepository::findOneByNumber()` looked the number
  up in every programme, though it is unique within one only, and `broadcast.programme` was read
  by nothing. `findOneByNumber()` takes the programme as a second, optional argument.
