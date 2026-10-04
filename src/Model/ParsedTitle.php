<?php

namespace Base\Broadcast\Model;

use Base\Broadcast\Entity\Performance;

/** What Service\TitleParser read in a film's title. */
final class ParsedTitle
{
    /**
     * @param list<string> $artists in the order of the billing
     * @param list<string> $notes   what else stood in brackets ("Inédit à la TV")
     */
    public function __construct(
        public readonly string $raw,
        public readonly string $kind,
        public readonly array $artists = [],
        public readonly ?string $song = null,
        public readonly ?string $originalArtist = null,
        public readonly ?int $year = null,
        public readonly array $notes = [],
        public readonly bool $recognised = true,
    ) {
    }

    public function isLive(): bool { return Performance::LIVE === $this->kind; }

    /** Somebody else's song: an original artist named who is not on stage. */
    public function isCover(): bool
    {
        if (null === $this->originalArtist) {
            return false;
        }
        $original = mb_strtolower($this->originalArtist);

        return !\in_array($original, array_map('mb_strtolower', $this->artists), true);
    }

    public function isDuet(): bool { return $this->isLive() && \count($this->artists) > 1; }
}
