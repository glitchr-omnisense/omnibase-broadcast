<?php

namespace Base\Broadcast\Service;

use Base\Broadcast\Entity\Profile;
use Base\Broadcast\Entity\Song;
use Base\Music\Entity\Performer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * The artists and the songs, found by their names or made: one Performer
 * (omnibase/music) and its Profile for "Jean-Louis Aubert" however often
 * he comes; one Song for a title and who made it known, whoever sings it.
 * What it makes in one run is remembered, so nothing is made twice before
 * the flush.
 */
class Catalogue
{
    /** @var array<string, Profile> */
    private array $profiles = [];

    /** @var array<string, Song> */
    private array $songs = [];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function slug(string $text): string
    {
        return strtolower((string) (new AsciiSlugger('fr'))->slug($text)) ?: 'sans-nom';
    }

    public function artist(string $name): Profile
    {
        $name = trim($name);
        $slug = self::slug($name);
        if (isset($this->profiles[$slug])) {
            return $this->profiles[$slug];
        }
        $profile = $this->entityManager->getRepository(Profile::class)->findOneBy(['slug' => $slug]);
        if (!$profile) {
            $performer = (new Performer())->setName($name);
            $profile = new Profile($performer, $slug);
            $this->entityManager->persist($performer);
            $this->entityManager->persist($profile);
        }

        return $this->profiles[$slug] = $profile;
    }

    /**
     * The song of that title. The same title by another original artist is
     * another song; a version that does not say whose it is joins the one
     * that exists.
     */
    public function song(string $title, ?string $originalArtist = null): Song
    {
        $base = self::slug($title);
        $repository = $this->entityManager->getRepository(Song::class);
        foreach ([$originalArtist ? $base.'-'.self::slug($originalArtist) : null, $base] as $slug) {
            if (null === $slug) {
                continue;
            }
            $song = $this->songs[$slug] ?? $repository->findOneBy(['slug' => $slug]);
            if ($song && (null === $originalArtist || null === $song->getOriginalArtist() || self::slug($song->getOriginalArtist()) === self::slug($originalArtist))) {
                if ($originalArtist && null === $song->getOriginalArtist()) {
                    $song->setOriginalArtist($originalArtist);
                }

                return $this->songs[$slug] = $song;
            }
        }
        $slug = mb_substr(isset($this->songs[$base]) || $repository->findOneBy(['slug' => $base]) ? $base.'-'.self::slug((string) $originalArtist) : $base, 0, 190);
        $song = new Song($title, $originalArtist, $slug);
        $this->entityManager->persist($song);

        return $this->songs[$slug] = $song;
    }

    /** Forget what was remembered (after an EntityManager::clear()). */
    public function reset(): void
    {
        $this->profiles = $this->songs = [];
    }
}
