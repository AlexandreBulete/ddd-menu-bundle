<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\Exception;

use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * A single zone (a main menu) holds one menu: its links are edited there,
 * not split into a second one.
 */
final class ZoneTakenException extends \DomainException
{
    public function __construct(
        public readonly Zone $zone,
        public readonly string $holder,
    ) {
        parent::__construct(sprintf('The menu "%s" already fills the zone "%s".', $holder, $zone->value()));
    }
}
