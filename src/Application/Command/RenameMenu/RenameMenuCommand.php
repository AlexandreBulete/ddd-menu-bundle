<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\RenameMenu;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;

/**
 * Changes the title shown above a menu's links.
 *
 * @implements CommandInterface<Menu>
 */
final readonly class RenameMenuCommand implements CommandInterface
{
    public function __construct(
        public MenuId $id,
        public string $title,
    ) {}
}
