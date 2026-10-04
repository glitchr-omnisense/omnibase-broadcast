<?php

namespace Base\Broadcast\Admin\Widget;

use Base\Admin\Config\Menu\MenuItem;
use Base\Admin\Widget\DashboardWidgetTypeInterface;
use Base\Broadcast\Entity\Episode;
use Base\Broadcast\Repository\PerformanceRepository;
use Doctrine\ORM\EntityManagerInterface;

/** The dashboard's tile: the titles the import did not understand (`MenuItem::block('broadcast_unrecognised', ...)`). */
final class BroadcastWidgetType implements DashboardWidgetTypeInterface
{
    public function __construct(private readonly PerformanceRepository $performances, private readonly EntityManagerInterface $entityManager)
    {
    }

    public static function getName(): string { return 'broadcast_unrecognised'; }

    public function getTemplate(): string { return '@Broadcast/admin/widget/unrecognised.html.twig'; }

    public function getTemplateVars(MenuItem $widget): array
    {
        return [
            'count' => $this->performances->countUnrecognised(),
            'unrecognised' => $this->performances->findUnrecognised(6),
            'episodes' => (int) $this->entityManager->createQueryBuilder()->select('COUNT(e.id)')->from(Episode::class, 'e')->getQuery()->getSingleScalarResult(),
        ];
    }
}
