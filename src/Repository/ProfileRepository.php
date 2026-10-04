<?php

namespace Base\Broadcast\Repository;

use Base\Broadcast\Entity\Profile;
use Base\Music\Entity\Performer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Profile> */
class ProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profile::class);
    }

    public function findOneFor(Performer $performer): ?Profile
    {
        return $this->findOneBy(['performer' => $performer]);
    }

    /** @return list<Profile> every artist, by name */
    public function findAllByName(): array
    {
        return $this->createQueryBuilder('f')->innerJoin('f.performer', 'p')->addSelect('p')->orderBy('p.name', 'ASC')->getQuery()->getResult();
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
