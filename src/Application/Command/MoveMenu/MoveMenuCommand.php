<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\MoveMenu;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * Puts a menu in another zone, or at another rank among its zone's menus.
 *
 * @implements CommandInterface<Menu>
 */
final readonly class MoveMenuCommand implements CommandInterface
{
    public function __construct(
        public MenuId $id,
        public Zone $zone,
        public int $position,
    ) {}
}
