<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\ChangeMenuItems;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuItem;

/**
 * Replaces a menu's links: adds, removes and reorders them at once.
 *
 * @implements CommandInterface<Menu>
 */
final readonly class ChangeMenuItemsCommand implements CommandInterface
{
    /**
     * @param list<MenuItem> $items
     */
    public function __construct(
        public MenuId $id,
        public array $items,
    ) {}
}
