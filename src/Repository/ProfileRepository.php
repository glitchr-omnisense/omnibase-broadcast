<?php

namespace Base\Broadcast\Repository;

use Base\Broadcast\Entity\Profile;
use Base\Music\Entity\Performer;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Profile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profile|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Profile[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Profile::class);
    }

    public function findOneFor(Performer $performer): ?Profile
    {
        return $this->findOneBy(['performer' => $performer]);
    }

    /** @return list<Profile> every artist, by name */
    public function findAllByName(): array
    {
        // The performer was fetch-joined; ordered by its name through the finder's join of it.
        return $this->findBy([], ['performer.name' => 'ASC']);
    }

    /**
     * @param iterable<Performer> $performers
     *
     * @return array<int, Profile> performer id => profile
     */
    public function findFor(iterable $performers): array
    {
        $ids = [];
        foreach ($performers as $performer) {
            $ids[] = $performer->getId();
        }
        $profiles = [];
        foreach ($ids ? $this->findBy(['performer' => $ids]) : [] as $profile) {
            $profiles[$profile->getPerformer()->getId()] = $profile;
        }

        return $profiles;
    }
}
