<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Query\FindMenu;

use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * @implements QueryInterface<Menu>
 */
final readonly class FindMenuQuery implements QueryInterface
{
    public function __construct(
        public MenuId $id,
    ) {}
}
