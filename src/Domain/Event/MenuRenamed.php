<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class MenuRenamed implements DomainEvent
{
    public function __construct(
        public string $menuId,
        public string $title,
    ) {}
}
