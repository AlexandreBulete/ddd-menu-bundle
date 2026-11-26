<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Link;

use AlexandreBulete\DddMenuBundle\Domain\ValueObject\LinkTarget;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuItem;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Turns stored links into printable ones, through the sources the
 * application registered. An address typed in is printed as is.
 */
final class LinkResolver
{
    /** @var array<string, LinkSourceInterface> */
    private array $sources = [];

    /**
     * @param iterable<LinkSourceInterface> $sources
     */
    public function __construct(
        #[AutowireIterator('ddd_menu.link_source')]
        iterable $sources,
    ) {
        foreach ($sources as $source) {
            $this->sources[$source->kind()] = $source;
        }
    }

    public function resolve(MenuItem $item): ?ResolvedLink
    {
        $target = $item->target;
        if ($target->isUrl()) {
            return new ResolvedLink((string) $item->label, $target->reference, preg_match('~^https?://~i', $target->reference) === 1);
        }

        $link = ($this->sources[$target->kind] ?? null)?->resolve($target->reference);
        if ($link === null) {
            return null;
        }

        return $item->label === null ? $link : new ResolvedLink($item->label, $link->url, $link->external);
    }

    /**
     * Every source's choices for the back office, grouped by source label:
     * value "kind:reference".
     *
     * @return array<string, array<string, string>>
     */
    public function choices(): array
    {
        $groups = [];
        foreach ($this->sources as $kind => $source) {
            $group = [];
            foreach ($source->choices() as $label => $reference) {
                $group[$label] = $kind . ':' . $reference;
            }
            if ($group !== []) {
                $groups[$source->label()] = $group;
            }
        }

        return $groups;
    }

    public static function target(string $choice): LinkTarget
    {
        [$kind, $reference] = explode(':', $choice, 2) + [1 => ''];

        return new LinkTarget($kind, $reference);
    }
}
