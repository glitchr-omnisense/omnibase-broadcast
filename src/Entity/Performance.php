<?php

namespace Base\Broadcast\Entity;

use Base\Broadcast\Repository\PerformanceRepository;
use Base\Music\Entity\Performer;
use Base\Video\Entity\Video;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * What one film of an episode is: a song played live (by whom, a cover or
 * their own, a duet), an interview, the whole show, a bonus. The film is
 * omnibase/video's; the people are omnibase/music's performers.
 */
#[ORM\Entity(repositoryClass: PerformanceRepository::class)]
#[ORM\Table(name: 'broadcast_performance')]
#[ORM\Index(columns: ['kind'], name: 'broadcast_performance_kind_idx')]
class Performance
{
    public const LIVE = 'live';
    public const INTERVIEW = 'interview';
    public const FULL = 'full';
    public const BONUS = 'bonus';
    public const KINDS = [self::LIVE, self::INTERVIEW, self::FULL, self::BONUS];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\ManyToOne(targetEntity: Episode::class, inversedBy: 'performances')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Episode $episode;

    #[ORM\OneToOne(targetEntity: Video::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Video $video;

    /** The order of passage in the episode. */
    #[ORM\Column(type: 'integer')]
    protected int $position = 0;

    #[ORM\Column(length: 16)]
    protected string $kind = self::LIVE;

    #[ORM\ManyToOne(targetEntity: Song::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Song $song = null;

    /** Somebody else's song. */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $cover = false;

    /** @var Collection<int, Performer> */
    #[ORM\ManyToMany(targetEntity: Performer::class)]
    #[ORM\JoinTable(name: 'broadcast_performance_performer')]
    protected Collection $performers;

    /** The title as it was written where it came from. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $rawTitle = null;

    /** Whether the title was understood (a live with its song and its artists); the others are listed in the back office. */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected bool $recognised = true;

    public function __construct(Episode $episode, Video $video, string $kind = self::LIVE, int $position = 0)
    {
        $this->episode = $episode;
        $this->video = $video;
        $this->kind = \in_array($kind, self::KINDS, true) ? $kind : self::BONUS;
        $this->position = $position;
        $this->performers = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) ($this->rawTitle ?? $this->video);
    }

    public function getId(): ?int { return $this->id; }
    public function getEpisode(): Episode { return $this->episode; }
    public function getVideo(): Video { return $this->video; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): self { $this->position = $position; return $this; }

    public function getKind(): string { return $this->kind; }
    public function setKind(string $kind): self { $this->kind = \in_array($kind, self::KINDS, true) ? $kind : self::BONUS; return $this; }
    public function isLive(): bool { return self::LIVE === $this->kind; }

    public function getSong(): ?Song { return $this->song; }
    public function setSong(?Song $song): self { $this->song = $song; return $this; }

    public function isCover(): bool { return $this->cover; }
    public function setCover(bool $cover): self { $this->cover = $cover; return $this; }

    /** Two artists or more on one song. */
    public function isDuet(): bool { return $this->isLive() && $this->performers->count() > 1; }

    /** @return Collection<int, Performer> */
    public function getPerformers(): Collection { return $this->performers; }

    public function addPerformer(Performer $performer): self
    {
        if (!$this->performers->contains($performer)) {
            $this->performers->add($performer);
        }

        return $this;
    }

    public function removePerformer(Performer $performer): self
    {
        $this->performers->removeElement($performer);

        return $this;
    }

    /** "Jean-Louis Aubert / Santa" */
    public function getBilling(): string
    {
        return implode(' / ', array_map(fn (Performer $p) => (string) $p->getName(), $this->performers->toArray()));
    }

    public function getRawTitle(): ?string { return $this->rawTitle; }
    public function setRawTitle(?string $rawTitle): self { $this->rawTitle = $rawTitle; return $this; }

    public function isRecognised(): bool { return $this->recognised; }
    public function setRecognised(bool $recognised): self { $this->recognised = $recognised; return $this; }
}
