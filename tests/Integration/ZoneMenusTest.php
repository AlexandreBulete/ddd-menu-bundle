<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Tests\Integration;

use AlexandreBulete\DddMenuBundle\Application\Command\CreateMenu\CreateMenuCommand;
use AlexandreBulete\DddMenuBundle\Application\Command\CreateMenu\CreateMenuHandler;
use AlexandreBulete\DddMenuBundle\Application\Command\MoveMenu\MoveMenuCommand;
use AlexandreBulete\DddMenuBundle\Application\Command\MoveMenu\MoveMenuHandler;
use AlexandreBulete\DddMenuBundle\Application\Service\ZoneGuard;
use AlexandreBulete\DddMenuBundle\Domain\Exception\UnknownZoneException;
use AlexandreBulete\DddMenuBundle\Domain\Exception\ZoneTakenException;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\LinkTarget;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuItem;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;
use AlexandreBulete\DddMenuBundle\Infrastructure\Config\ConfiguredZoneCatalog;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\DoctrineMenuRepository;
use AlexandreBulete\DddMenuBundle\Tests\Integration\Fixture\NullEventPublisher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ZoneMenusTest extends TestCase
{
    private EntityManagerInterface $em;
    private DoctrineMenuRepository $menus;
    private ZoneGuard $guard;
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->em = MenuDatabase::migrated();
        $this->menus = new DoctrineMenuRepository($this->em, new NullEventPublisher());
        $this->guard = new ZoneGuard($this->menus, new ConfiguredZoneCatalog([
            'main' => ['label' => 'Main', 'single' => true],
            'footer' => ['label' => 'Footer', 'single' => false],
        ]));
        $this->clock = new MockClock('2026-11-27 10:00:00');
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    #[Test]
    public function a_zone_lists_its_menus_in_order_with_their_links(): void
    {
        $this->create('footer', 'Légal', 2, [new MenuItem(new LinkTarget('page', '01JD0000000000000000000000'))]);
        $this->create('footer', 'Le site', 1, [
            new MenuItem(new LinkTarget('route', 'home'), 'Accueil'),
            new MenuItem(LinkTarget::url('/a-propos#contact'), 'Contact'),
        ]);
        $this->em->clear();

        $menus = $this->menus->inZone(Zone::fromString('footer'));
        self::assertSame(['Le site', 'Légal'], array_map(static fn (Menu $m): string => $m->title, $menus));
        self::assertSame(['Accueil', 'Contact'], array_map(static fn (MenuItem $i): ?string => $i->label, $menus[0]->items));
        self::assertSame('/a-propos#contact', $menus[0]->items[1]->target->reference);
        self::assertNull($menus[1]->items[0]->label);
    }

    #[Test]
    public function a_single_zone_holds_one_menu(): void
    {
        $this->create('main', '', 0);
        $other = $this->create('footer', 'Le site', 0);

        try {
            $this->create('main', 'Second', 1);
            self::fail('Expected the zone to be taken.');
        } catch (ZoneTakenException) {
        }
        $this->expectException(ZoneTakenException::class);
        (new MoveMenuHandler($this->menus, $this->guard, $this->clock))(new MoveMenuCommand($other->id, Zone::fromString('main'), 0));
    }

    #[Test]
    public function a_menu_goes_only_where_the_site_prints_menus(): void
    {
        $this->expectException(UnknownZoneException::class);
        $this->create('sidebar', 'Ailleurs', 0);
    }

    /**
     * @param list<MenuItem> $items
     */
    private function create(string $zone, string $title, int $position, array $items = []): Menu
    {
        return (new CreateMenuHandler($this->menus, $this->guard, $this->clock))(new CreateMenuCommand(Zone::fromString($zone), $title, $position, $items));
    }
}
