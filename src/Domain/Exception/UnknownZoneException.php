<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\Exception;

use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

final class UnknownZoneException extends \DomainException
{
    public function __construct(
        public readonly Zone $zone,
    ) {
        parent::__construct(sprintf('The site offers no menu zone "%s".', $zone->value()));
    }
}
