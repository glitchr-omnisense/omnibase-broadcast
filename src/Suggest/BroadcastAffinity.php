<?php

namespace Base\Broadcast\Suggest;

use Base\Broadcast\Repository\PerformanceRepository;
use Base\Video\Suggest\AffinityInterface;

/**
 * What makes two films of a show close, for omnibase/video's suggestions:
 * the same artist (as strong as the same channel: 3) and the same episode
 * (as strong as the same list: 2).
 */
final class BroadcastAffinity implements AffinityInterface
{
    public function __construct(private readonly PerformanceRepository $performances)
    {
    }

    public function groups(array $videos): array
    {
        $groups = [];
        foreach ($this->performances->findGroups(array_map(fn ($video) => $video->getId(), $videos)) as $videoId => $of) {
            $groups[$videoId]['episode:'.$of['episode']] = 2.0;
            foreach ($of['performers'] as $performer) {
                $groups[$videoId]['artist:'.$performer] = 3.0;
            }
        }

        return $groups;
    }
}
