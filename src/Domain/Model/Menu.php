<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\Model;

use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;
use AlexandreBulete\DddMenuBundle\Domain\Event\MenuCreated;
use AlexandreBulete\DddMenuBundle\Domain\Event\MenuDeleted;
use AlexandreBulete\DddMenuBundle\Domain\Event\MenuItemsChanged;
use AlexandreBulete\DddMenuBundle\Domain\Event\MenuMoved;
use AlexandreBulete\DddMenuBundle\Domain\Event\MenuRenamed;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuItem;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * A titled list of links, in a zone of the site (the main menu, a column of
 * the footer), at a position among the zone's menus. Its links keep the
 * order they were given.
 */
final class Menu
{
    use RecordsEvents;

    public const int TITLE_MAX_LENGTH = 100;
    public const int MAX_ITEMS = 50;

    /**
     * @param list<MenuItem> $items
     */
    private function __construct(
        private(set) MenuId $id,
        private(set) Zone $zone,
        private(set) string $title,
        private(set) int $position,
        private(set) array $items,
        private(set) \DateTimeImmutable $createdAt,
        private(set) \DateTimeImmutable $updatedAt,
    ) {}

    /**
     * The use case checks the zone exists, and that a single one is free.
     */
    public static function create(MenuId $id, Zone $zone, string $title, int $position, \DateTimeImmutable $at): self
    {
        $menu = new self($id, $zone, self::assertTitle($title), self::assertPosition($position), [], $at, $at);
        $menu->recordEvent(new MenuCreated((string) $id, $zone->value()));

        return $menu;
    }

    public function rename(string $title, \DateTimeImmutable $at): void
    {
        $title = self::assertTitle($title);
        if ($title === $this->title) {
            return;
        }

        $this->title = $title;
        $this->updatedAt = $at;
        $this->recordEvent(new MenuRenamed((string) $this->id, $title));
    }

    /**
     * Another zone, or another rank in the same one. The use case checks the
     * zone, as on creation.
     */
    public function move(Zone $zone, int $position, \DateTimeImmutable $at): void
    {
        $position = self::assertPosition($position);
        if ($zone->equals($this->zone) && $position === $this->position) {
            return;
        }

        $this->zone = $zone;
        $this->position = $position;
        $this->updatedAt = $at;
        $this->recordEvent(new MenuMoved((string) $this->id, $zone->value(), $position));
    }

    /**
     * The links, in the order given: replacing them is how one adds, removes
     * or reorders a link.
     *
     * @param list<MenuItem> $items
     */
    public function changeItems(array $items, \DateTimeImmutable $at): void
    {
        if (count($items) > self::MAX_ITEMS) {
            throw new \InvalidArgumentException(sprintf('A menu holds %d links at most.', self::MAX_ITEMS));
        }
        if (self::same($items, $this->items)) {
            return;
        }

        $this->items = $items;
        $this->updatedAt = $at;
        $this->recordEvent(new MenuItemsChanged((string) $this->id));
    }

    public function delete(): void
    {
        $this->recordEvent(new MenuDeleted((string) $this->id, $this->zone->value()));
    }

    /**
     * @param list<MenuItem> $a
     * @param list<MenuItem> $b
     */
    private static function same(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }
        foreach ($a as $i => $item) {
            if (!$item->equals($b[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * A main menu has no title to show: empty is allowed.
     */
    private static function assertTitle(string $title): string
    {
        $title = trim($title);
        if (mb_strlen($title) > self::TITLE_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A menu title has %d characters at most.', self::TITLE_MAX_LENGTH));
        }

        return $title;
    }

    private static function assertPosition(int $position): int
    {
        if ($position < 0 || $position > 999) {
            throw new \InvalidArgumentException('A menu position is between 0 and 999.');
        }

        return $position;
    }
}
