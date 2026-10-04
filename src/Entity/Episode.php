<?php

namespace Base\Broadcast\Entity;

use Base\Broadcast\Repository\EpisodeRepository;
use Base\Video\Entity\Video;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * One broadcast of the programme: its number, the day it aired, its
 * picture, the whole show as one film when there is one, and what was
 * played, in order (its performances).
 */
#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
#[ORM\Table(name: 'broadcast_episode')]
#[ORM\UniqueConstraint(name: 'broadcast_episode_number', columns: ['programme_id', 'number'])]
#[ORM\Index(columns: ['airedAt'], name: 'broadcast_episode_aired_idx')]
class Episode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\ManyToOne(targetEntity: Programme::class)]
    #[ORM\JoinColumn(name: 'programme_id', nullable: false, onDelete: 'CASCADE')]
    protected Programme $programme;

    #[ORM\Column(type: 'integer')]
    protected int $number;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(type: 'date', nullable: true)]
    protected ?\DateTimeInterface $airedAt = null;

    /** Its picture: an address (shown from where it is, never copied). */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $image = null;

    /** The whole show, as one film. */
    #[ORM\ManyToOne(targetEntity: Video::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Video $full = null;

    /** Where it came from, when it was imported. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $origin = null;

    /** @var Collection<int, Performance> */
    #[ORM\OneToMany(targetEntity: Performance::class, mappedBy: 'episode', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC', 'id' => 'ASC'])]
    protected Collection $performances;

    public function __construct(Programme $programme, int $number)
    {
        $this->programme = $programme;
        $this->number = $number;
        $this->performances = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?: sprintf('%s n°%d', $this->programme->getName(), $this->number);
    }

    public function getId(): ?int { return $this->id; }
    public function getProgramme(): Programme { return $this->programme; }
    public function getNumber(): int { return $this->number; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title ?: null; return $this; }

    public function getAiredAt(): ?\DateTimeInterface { return $this->airedAt; }
    public function setAiredAt(?\DateTimeInterface $airedAt): self { $this->airedAt = $airedAt; return $this; }
    public function getYear(): ?int { return $this->airedAt ? (int) $this->airedAt->format('Y') : null; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image ?: null; return $this; }

    public function getFull(): ?Video { return $this->full; }
    public function setFull(?Video $full): self { $this->full = $full; return $this; }

    public function getOrigin(): ?string { return $this->origin; }
    public function setOrigin(?string $origin): self { $this->origin = $origin ?: null; return $this; }

    /** @return Collection<int, Performance> */
    public function getPerformances(): Collection { return $this->performances; }

    public function addPerformance(Performance $performance): self
    {
        if (!$this->performances->contains($performance)) {
            $this->performances->add($performance);
        }

        return $this;
    }

    /** @return list<Performance> the performances of one kind (Performance::LIVE...), in order */
    public function getByKind(string ...$kinds): array
    {
        return array_values($this->performances->filter(fn (Performance $p) => \in_array($p->getKind(), $kinds, true))->toArray());
    }

    /** @return list<\Base\Music\Entity\Performer> who was on stage, once each, in order of appearance */
    public function getPerformers(): array
    {
        $performers = [];
        foreach ($this->performances as $performance) {
            foreach ($performance->getPerformers() as $performer) {
                $performers[spl_object_id($performer)] = $performer;
            }
        }

        return array_values($performers);
    }
}
