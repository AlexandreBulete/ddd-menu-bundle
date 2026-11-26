<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Query\FindZoneMenus;

use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;

#[AsQueryHandler]
final readonly class FindZoneMenusHandler
{
    public function __construct(
        private MenuRepositoryInterface $menus,
    ) {}

    /**
     * @return list<Menu>
     */
    public function __invoke(FindZoneMenusQuery $query): array
    {
        return $this->menus->inZone($query->zone);
    }
}
