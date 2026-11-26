<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Link;

/**
 * A kind of destination the back office offers for a link: the site's own
 * pages (routes), the pages of a page bundle, the posts of a blog… An
 * application adds one by implementing this interface (autoconfigured).
 */
interface LinkSourceInterface
{
    /**
     * Stored with each link: lowercase letters, digits, underscores.
     */
    public function kind(): string;

    /**
     * The heading of this source's choices in the back office.
     */
    public function label(): string;

    /**
     * What the back office offers: label => reference.
     *
     * @return array<string, string>
     */
    public function choices(): array;

    /**
     * The link as printed, or null when the destination no longer exists or
     * is not public: the menu then leaves the link out.
     */
    public function resolve(string $reference): ?ResolvedLink;
}
