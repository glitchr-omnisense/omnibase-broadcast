<?php

namespace Base\Broadcast\Entity;

use Base\Broadcast\Repository\SongRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A song as it was played on stage, whoever played it: its title and, when
 * it is somebody else's, who made it known. Its page gathers every version.
 */
#[ORM\Entity(repositoryClass: SongRepository::class)]
#[ORM\Table(name: 'broadcast_song')]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(length: 190, unique: true)]
    protected ?string $slug = null;

    #[ORM\Column(length: 255)]
    protected ?string $title = null;

    /** Who made it known ("France Gall"); null: its performers' own. */
    #[ORM\Column(length: 190, nullable: true)]
    protected ?string $originalArtist = null;

    public function __construct(?string $title = null, ?string $originalArtist = null, ?string $slug = null)
    {
        $this->title = $title;
        $this->originalArtist = $originalArtist ?: null;
        $this->slug = $slug;
    }

    public function __toString(): string { return (string) $this->title; }

    public function getId(): ?int { return $this->id; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(?string $slug): self { $this->slug = $slug; return $this; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title; return $this; }
    public function getOriginalArtist(): ?string { return $this->originalArtist; }
    public function setOriginalArtist(?string $originalArtist): self { $this->originalArtist = $originalArtist ?: null; return $this; }
}
