<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Config;

use AlexandreBulete\DddMenuBundle\Domain\Service\ZoneCatalogInterface;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

/**
 * The zones declared under `menu.zones`.
 */
final readonly class ConfiguredZoneCatalog implements ZoneCatalogInterface
{
    /**
     * @param array<string, array{label: string, single: bool}> $zones
     */
    public function __construct(
        private array $zones = [],
    ) {}

    public function has(Zone $zone): bool
    {
        return isset($this->zones[$zone->value()]);
    }

    public function isSingle(Zone $zone): bool
    {
        return $this->zones[$zone->value()]['single'] ?? false;
    }

    /**
     * For a choice field: label => key, in declaration order.
     *
     * @return array<string, string>
     */
    public function choices(): array
    {
        $choices = [];
        foreach ($this->zones as $key => $zone) {
            $choices[$zone['label']] = $key;
        }

        return $choices;
    }

    public function label(string $key): ?string
    {
        return $this->zones[$key]['label'] ?? null;
    }
}
