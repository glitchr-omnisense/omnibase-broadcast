# Changelog

## [Unreleased]

### Fixed
- `/emission/{number}` shows the episode of that number in the site's programme
  (`broadcast.programme`, else the first): `EpisodeRepository::findOneByNumber()` looked the number
  up in every programme, though it is unique within one only, and `broadcast.programme` was read
  by nothing. `findOneByNumber()` takes the programme as a second, optional argument.
