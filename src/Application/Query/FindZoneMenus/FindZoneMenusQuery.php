<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Query\FindZoneMenus;

use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * The menus a template prints in a zone, by position.
 *
 * @implements QueryInterface<list<Menu>>
 */
#[PublicAccess]
final readonly class FindZoneMenusQuery implements QueryInterface
{
    public function __construct(
        public Zone $zone,
    ) {}
}
