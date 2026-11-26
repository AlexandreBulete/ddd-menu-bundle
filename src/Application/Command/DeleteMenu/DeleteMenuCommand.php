<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\DeleteMenu;

use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;

/**
 * Removes a menu and its links. What they point to stays.
 *
 * @implements CommandInterface<void>
 */
final readonly class DeleteMenuCommand implements CommandInterface
{
    public function __construct(
        public MenuId $id,
    ) {}
}
