<?php

namespace Base\Broadcast\Entity;

use Base\Broadcast\Repository\ProgrammeRepository;
use Base\Video\Entity\Channel;
use Doctrine\ORM\Mapping as ORM;

/** The show: its name, what it says of itself, the channel its films are on. */
#[ORM\Entity(repositoryClass: ProgrammeRepository::class)]
#[ORM\Table(name: 'broadcast_programme')]
class Programme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(length: 80, unique: true)]
    protected ?string $slug = null;

    #[ORM\Column(length: 160)]
    protected ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Channel::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Channel $channel = null;

    public function __construct(?string $name = null, ?string $slug = null)
    {
        $this->name = $name;
        $this->slug = $slug ?? (null !== $name ? Channel::slugify($name) : null);
    }

    public function __toString(): string { return (string) $this->name; }

    public function getId(): ?int { return $this->id; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(?string $slug): self { $this->slug = $slug; return $this; }
    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description ?: null; return $this; }
    public function getChannel(): ?Channel { return $this->channel; }
    public function setChannel(?Channel $channel): self { $this->channel = $channel; return $this; }
}
