# Changelog

## [Unreleased] - 1.0.0

omnibase/broadcast is a broadcast's site on omnisense/omnibase: a programme, its episodes, and for each film of an
episode what it is - a song played live (by whom, a cover, a duet), an interview, the whole show, a bonus. It names only
`Base\` classes: its repositories extend `Base\Database\Repository\ServiceEntityRepository` and speak the core's finder
language, its services write through `Base\Contract\UnitOfWork` and read other classes through
`Base\Database\Repository\Repositories`. Each query is written once in the finder language, the songs with several
versions and the duets included (`findValuesBy()` with an aggregate and a `having`), and compiled by the engine of the
persistence chosen (omnibridge/doctrine or omnibridge/eloquent).
