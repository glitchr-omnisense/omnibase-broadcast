---
title: "Testing"
order: 60
---

# Testing

## Running the suite

From the package's checkout, with its dev dependencies:

```bash
vendor/bin/phpunit
```

In omnisense/omnibase-harness, mounting the checkout in place of the
installed package:

```bash
docker run --rm --network none \
    -v "$PWD":/srv/demo/vendor/omnibase/broadcast:ro \
    omnisense/omnibase-harness:latest test broadcast
```

On this checkout, in the harness:

```text
Tests: 36, Assertions: 262, Errors: 1.
```

The error is `AffinityTest`'s: the harness has no omnibase/video, so
`Base\Video\Suggest\AffinityInterface` is not found. With omnibase/video and
omnibase/music on the autoloader (a host that has them), that test passes
(3 tests, 10 assertions in the harness with both checkouts mounted).

## What the tests check

| Test | Checks |
|---|---|
| `Service\TitleParserTest` | 24 of the show's own titles (`tests/fixtures/titles.json`) read as expected: lives, duets, covers, medleys, notes, brackets and quotes left open, interviews, bonus, whole shows, a title not recognised; curly and French quotes; an artist singing their own song named in brackets is no cover; without a section the title says the kind |
| `Service\AffinityTest` | the same artist weighs as a channel, the same episode as a list |
| `Controller\CrudRequiredFieldsTest` | omnibase/admin's `CrudFieldsTestCase` on the five screens |
