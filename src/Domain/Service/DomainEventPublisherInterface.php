<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\Service;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * Where the repository hands the events a menu recorded, once it is written,
 * in the same transaction.
 */
interface DomainEventPublisherInterface
{
    /**
     * @param iterable<DomainEvent> $events
     */
    public function publishAll(iterable $events): void;
}
