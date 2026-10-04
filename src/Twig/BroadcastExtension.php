<?php

namespace Base\Broadcast\Twig;

use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Entity\Profile;
use Base\Broadcast\Repository\PerformanceRepository;
use Base\Broadcast\Repository\ProfileRepository;
use Base\Video\Entity\Video;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 *   broadcast_performance(video)   what a film is (its episode, its song, its artists), or null
 *   broadcast_profiles(performers) performer id => its page (Profile), for links to the artists
 */
class BroadcastExtension extends AbstractExtension
{
    public function __construct(
        private readonly PerformanceRepository $performances,
        private readonly ProfileRepository $profiles,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('broadcast_performance', fn (Video $video): ?Performance => $this->performances->findOneForVideo($video)),
            new TwigFunction('broadcast_profiles', fn (iterable $performers): array => $this->profiles->findFor($performers)),
        ];
    }
}
