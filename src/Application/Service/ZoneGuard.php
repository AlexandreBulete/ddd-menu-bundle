<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Service;

use AlexandreBulete\DddMenuBundle\Domain\Exception\UnknownZoneException;
use AlexandreBulete\DddMenuBundle\Domain\Exception\ZoneTakenException;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use AlexandreBulete\DddMenuBundle\Domain\Service\ZoneCatalogInterface;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * Creating a menu and moving one ask the same: the zone exists, and a single
 * zone holds no other menu.
 */
final readonly class ZoneGuard
{
    public function __construct(
        private MenuRepositoryInterface $menus,
        private ZoneCatalogInterface $zones,
    ) {}

    public function assertCanHold(Zone $zone, ?MenuId $menu = null): void
    {
        if (!$this->zones->has($zone)) {
            throw new UnknownZoneException($zone);
        }
        if (!$this->zones->isSingle($zone)) {
            return;
        }
        foreach ($this->menus->inZone($zone) as $holder) {
            if ($menu === null || !$holder->id->equals($menu)) {
                throw new ZoneTakenException($zone, $holder->title !== '' ? $holder->title : $zone->value());
            }
        }
    }
}
