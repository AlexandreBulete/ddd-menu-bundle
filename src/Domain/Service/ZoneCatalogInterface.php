<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\Service;

use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * The zones a site's templates print. A single zone holds one menu (a main
 * menu); the others, as many as needed (the columns of a footer).
 */
interface ZoneCatalogInterface
{
    public function has(Zone $zone): bool;

    public function isSingle(Zone $zone): bool;
}
