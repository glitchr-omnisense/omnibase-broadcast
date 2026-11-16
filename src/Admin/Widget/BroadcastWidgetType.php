<?php

namespace Base\Broadcast\Admin\Widget;

use Base\Admin\Config\Menu\MenuItem;
use Base\Admin\Widget\DashboardWidgetTypeInterface;
use Base\Broadcast\Repository\PerformanceRepository;
use Base\Broadcast\Repository\EpisodeRepository;

/** The dashboard's tile: the titles the import did not understand (`MenuItem::block('broadcast_unrecognised', ...)`). */
final class BroadcastWidgetType implements DashboardWidgetTypeInterface
{
    public function __construct(private readonly PerformanceRepository $performances, private readonly EpisodeRepository $episodes)
    {
    }

    public static function getName(): string { return 'broadcast_unrecognised'; }

    public function getTemplate(): string { return '@Broadcast/admin/widget/unrecognised.html.twig'; }

    public function getTemplateVars(MenuItem $widget): array
    {
        return [
            'count' => $this->performances->countUnrecognised(),
            'unrecognised' => $this->performances->findUnrecognised(6),
            'episodes' => $this->episodes->count(),
        ];
    }
}
