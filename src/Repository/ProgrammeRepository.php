<?php

namespace Base\Broadcast\Repository;

use Base\Broadcast\Entity\Programme;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Programme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Programme|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Programme[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class ProgrammeRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Programme::class);
    }

    /** The site's programme: the one named, or the first. */
    public function findCurrent(?string $slug = null): ?Programme
    {
        return $slug ? $this->findOneBy(['slug' => $slug]) : $this->findOneBy([], ['id' => 'ASC']);
    }
}
