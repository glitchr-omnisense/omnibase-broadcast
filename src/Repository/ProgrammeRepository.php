<?php

namespace Base\Broadcast\Repository;

use Base\Broadcast\Entity\Programme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Programme> */
class ProgrammeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Programme::class);
    }

    /** The site's programme: the one named, or the first. */
    public function findCurrent(?string $slug = null): ?Programme
    {
        return $slug ? $this->findOneBy(['slug' => $slug]) : $this->findOneBy([], ['id' => 'ASC']);
    }
}
