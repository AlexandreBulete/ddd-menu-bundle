<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Tests\Integration\Fixture;

use AlexandreBulete\DddMenuBundle\Domain\Service\DomainEventPublisherInterface;

final class NullEventPublisher implements DomainEventPublisherInterface
{
    public function publishAll(iterable $events): void {}
}
