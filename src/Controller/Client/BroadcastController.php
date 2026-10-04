<?php

namespace Base\Broadcast\Controller\Client;

use Base\Broadcast\Entity\Performance;
use Base\Broadcast\Repository\EpisodeRepository;
use Base\Broadcast\Repository\PerformanceRepository;
use Base\Broadcast\Repository\ProfileRepository;
use Base\Broadcast\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The show's pages: its episodes by year, one episode (the whole show and
 * the order of passage), the artists, one artist, one song and every
 * version of it played on stage, the covers and the duets.
 */
class BroadcastController extends AbstractController
{
    public function __construct(
        private readonly EpisodeRepository $episodes,
        private readonly PerformanceRepository $performances,
        private readonly ProfileRepository $profiles,
        private readonly SongRepository $songs,
    ) {
    }

    #[Route('/emissions', name: 'broadcast_episodes', methods: ['GET'])]
    public function episodes(Request $request): Response
    {
        $years = $this->episodes->countByYear();
        $year = $request->query->getInt('annee') ?: (array_key_first($years) ?? (int) date('Y'));

        return $this->render('@Broadcast/client/episodes.html.twig', [
            'years' => $years,
            'decades' => self::decades(array_keys($years)),
            'year' => $year,
            'episodes' => $this->episodes->findByYear($year),
        ]);
    }

    #[Route('/emission/{number}', name: 'broadcast_episode', requirements: ['number' => '\d+'], methods: ['GET'])]
    public function episode(int $number): Response
    {
        $episode = $this->episodes->findOneByNumber($number) ?? throw new NotFoundHttpException('No such episode.');

        return $this->render('@Broadcast/client/episode.html.twig', [
            'episode' => $episode,
            'lives' => $episode->getByKind(Performance::LIVE),
            'interviews' => $episode->getByKind(Performance::INTERVIEW),
            'bonus' => $episode->getByKind(Performance::BONUS),
            'profiles' => $this->profiles->findFor($episode->getPerformers()),
        ]);
    }

    #[Route('/artistes', name: 'broadcast_artists', methods: ['GET'])]
    public function artists(): Response
    {
        $letters = [];
        foreach ($this->profiles->findAllByName() as $profile) {
            $letter = mb_strtoupper(mb_substr(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $profile->getName()) ?: $profile->getName(), 0, 1));
            $letters[ctype_alpha($letter) ? $letter : '#'][] = $profile;
        }
        ksort($letters);

        return $this->render('@Broadcast/client/artists.html.twig', ['letters' => $letters]);
    }

    #[Route('/artiste/{slug}', name: 'broadcast_artist', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function artist(string $slug): Response
    {
        $profile = $this->profiles->findOneBy(['slug' => $slug]) ?? throw new NotFoundHttpException('No such artist.');
        $performer = $profile->getPerformer();

        return $this->render('@Broadcast/client/artist.html.twig', [
            'profile' => $profile,
            'lives' => $this->performances->findForPerformer($performer, [Performance::LIVE]),
            'others' => $this->performances->findForPerformer($performer, [Performance::INTERVIEW, Performance::BONUS]),
        ]);
    }

    #[Route('/chansons', name: 'broadcast_songs', methods: ['GET'])]
    public function songs(): Response
    {
        return $this->render('@Broadcast/client/songs.html.twig', ['songs' => $this->songs->findWithSeveralVersions(60)]);
    }

    #[Route('/chanson/{slug}', name: 'broadcast_song', requirements: ['slug' => '[a-z0-9-]+'], methods: ['GET'])]
    public function song(string $slug): Response
    {
        $song = $this->songs->findOneBy(['slug' => $slug]) ?? throw new NotFoundHttpException('No such song.');
        $versions = $this->performances->findForSong($song);
        $performers = [];
        foreach ($versions as $version) {
            foreach ($version->getPerformers() as $performer) {
                $performers[] = $performer;
            }
        }

        return $this->render('@Broadcast/client/song.html.twig', ['song' => $song, 'versions' => $versions, 'profiles' => $this->profiles->findFor($performers)]);
    }

    #[Route('/reprises', name: 'broadcast_covers', methods: ['GET'])]
    public function covers(): Response
    {
        return $this->render('@Broadcast/client/list.html.twig', ['key' => 'covers', 'performances' => $this->performances->findCovers(60)]);
    }

    #[Route('/duos', name: 'broadcast_duets', methods: ['GET'])]
    public function duets(): Response
    {
        return $this->render('@Broadcast/client/list.html.twig', ['key' => 'duets', 'performances' => $this->performances->findDuets(60)]);
    }

    /**
     * @param list<int> $years
     *
     * @return array<int, list<int>> decade (1990) => its years, the latest decade first
     */
    public static function decades(array $years): array
    {
        $decades = [];
        foreach ($years as $year) {
            $decades[intdiv($year, 10) * 10][] = $year;
        }
        krsort($decades);

        return array_map(function (array $ys) { sort($ys); return $ys; }, $decades);
    }
}
