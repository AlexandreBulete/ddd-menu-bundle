<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\Repository;

use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * @extends RepositoryInterface<Menu>
 */
interface MenuRepositoryInterface extends RepositoryInterface
{
    public function save(Menu $menu): void;

    public function remove(Menu $menu): void;

    /**
     * The menus of a zone, by position, then title.
     *
     * @return list<Menu>
     */
    public function inZone(Zone $zone): array;
}
