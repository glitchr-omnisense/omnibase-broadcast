<?php

namespace Base\Broadcast\Entity;

use Base\Broadcast\Repository\ProfileRepository;
use Base\Music\Entity\Performer;
use Doctrine\ORM\Mapping as ORM;

/**
 * An artist's page on the site. The artist is omnibase/music's Performer
 * (a name, a kind); this is what a page of their own adds: its address,
 * their story, their picture.
 */
#[ORM\Entity(repositoryClass: ProfileRepository::class)]
#[ORM\Table(name: 'broadcast_profile')]
class Profile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\OneToOne(targetEntity: Performer::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Performer $performer;

    #[ORM\Column(length: 190, unique: true)]
    protected string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $biography = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $image = null;

    /** Whether the biography was looked for where the artist came from (an import asks once). */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected bool $looked = false;

    public function __construct(Performer $performer, string $slug)
    {
        $this->performer = $performer;
        $this->slug = $slug;
    }

    public function __toString(): string { return (string) $this->performer->getName(); }

    public function getId(): ?int { return $this->id; }
    public function getPerformer(): Performer { return $this->performer; }
    public function getName(): string { return (string) $this->performer->getName(); }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }
    public function getBiography(): ?string { return $this->biography; }
    public function setBiography(?string $biography): self { $this->biography = $biography ? trim($biography) : null; return $this; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image ?: null; return $this; }
    public function isLooked(): bool { return $this->looked; }
    public function setLooked(bool $looked): self { $this->looked = $looked; return $this; }
}
