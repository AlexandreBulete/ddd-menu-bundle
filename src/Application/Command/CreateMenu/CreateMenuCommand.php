<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\CreateMenu;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuItem;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * @implements CommandInterface<Menu>
 */
final readonly class CreateMenuCommand implements CommandInterface
{
    /**
     * @param list<MenuItem> $items
     */
    public function __construct(
        public Zone $zone,
        public string $title,
        public int $position = 0,
        public array $items = [],
    ) {}
}
