<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class MenuDeleted implements DomainEvent
{
    public function __construct(
        public string $menuId,
        public string $zone,
    ) {}
}
