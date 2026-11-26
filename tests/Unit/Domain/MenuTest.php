<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Tests\Unit\Domain;

use AlexandreBulete\DddMenuBundle\Domain\Event\MenuCreated;
use AlexandreBulete\DddMenuBundle\Domain\Event\MenuItemsChanged;
use AlexandreBulete\DddMenuBundle\Domain\Event\MenuMoved;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\LinkTarget;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuItem;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MenuTest extends TestCase
{
    #[Test]
    public function only_changes_are_recorded_and_dated(): void
    {
        $at = new \DateTimeImmutable('2026-11-27 10:00:00');
        $later = $at->modify('+1 hour');
        $menu = Menu::create(MenuId::generate(), Zone::fromString('footer'), ' Légal ', 0, $at);
        self::assertSame('Légal', $menu->title);
        self::assertInstanceOf(MenuCreated::class, $menu->releaseEvents()[0]);

        $menu->rename('Légal', $later);
        $menu->move(Zone::fromString('footer'), 0, $later);
        $menu->changeItems([], $later);
        self::assertSame([], $menu->releaseEvents());
        self::assertEquals($at, $menu->updatedAt);

        $menu->changeItems([new MenuItem(new LinkTarget('route', 'home'))], $later);
        $menu->changeItems([new MenuItem(new LinkTarget('route', 'home'))], $later);
        $menu->move(Zone::fromString('footer'), 2, $later);
        $events = $menu->releaseEvents();
        self::assertCount(2, $events);
        self::assertInstanceOf(MenuItemsChanged::class, $events[0]);
        self::assertInstanceOf(MenuMoved::class, $events[1]);
        self::assertEquals($later, $menu->updatedAt);
    }

    #[Test]
    public function a_link_to_an_address_has_a_label(): void
    {
        self::assertNull((new MenuItem(new LinkTarget('page', 'x'), '  '))->label);
        $this->expectException(\InvalidArgumentException::class);
        new MenuItem(LinkTarget::url('https://example.test'));
    }

    #[Test]
    #[DataProvider('addresses')]
    public function an_address_is_absolute_or_a_path(string $address, bool $valid): void
    {
        self::assertSame($valid, LinkTarget::isAddress($address));
    }

    /**
     * @return iterable<array{string, bool}>
     */
    public static function addresses(): iterable
    {
        yield ['https://example.test/page', true];
        yield ['mailto:hello@example.test', true];
        yield ['/a-propos#contact', true];
        yield ['//evil.example', false];
        yield ['javascript:alert(1)', false];
        yield ['example.test', false];
    }
}
