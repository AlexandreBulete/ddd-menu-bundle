<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Link;

/**
 * A link as a template prints it.
 */
final readonly class ResolvedLink
{
    public function __construct(
        public string $label,
        public string $url,
        public bool $external = false,
    ) {}
}
