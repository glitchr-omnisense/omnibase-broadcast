<?php

namespace Base\Broadcast\Tests\Repository;

use Base\Broadcast\Entity\Programme;
use Base\Broadcast\Repository\EpisodeRepository;
use PHPUnit\Framework\TestCase;

/**
 * An episode is looked up by its number within the site's programme (broadcast.programme, else
 * the first): the number is unique within one programme only (broadcast_episode_number), and
 * findOneByNumber() looked it up in every programme, showing whichever came first.
 */
final class EpisodeByNumberTest extends TestCase
{
    public function testTheNumberIsLookedUpInTheProgramme(): void
    {
        $programme = (new \ReflectionClass(Programme::class))->newInstanceWithoutConstructor();
        $repository = $this->getMockBuilder(EpisodeRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBy'])->getMock();
        $repository->expects(self::exactly(2))->method('findOneBy')->willReturnCallback(function (array $criteria) use ($programme) {
            static $call = 0;
            match (++$call) {
                1 => self::assertSame(['number' => 12, 'programme' => $programme], $criteria),
                2 => self::assertSame(['number' => 12], $criteria, 'no programme: any'),
            };

            return null;
        });

        $repository->findOneByNumber(12, $programme);
        $repository->findOneByNumber(12);
    }
}
