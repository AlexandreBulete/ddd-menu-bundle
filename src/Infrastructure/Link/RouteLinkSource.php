<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Link;

use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The site's own pages, declared under `menu.routes` (route => label):
 * routes without parameters.
 */
final readonly class RouteLinkSource implements LinkSourceInterface
{
    public const string KIND = 'route';

    /**
     * @param array<string, string> $routes
     */
    public function __construct(
        private UrlGeneratorInterface $urls,
        private array $routes = [],
    ) {}

    public function kind(): string
    {
        return self::KIND;
    }

    public function label(): string
    {
        return 'menu.link.routes';
    }

    public function choices(): array
    {
        return array_flip($this->routes);
    }

    public function resolve(string $reference): ?ResolvedLink
    {
        if (!isset($this->routes[$reference])) {
            return null;
        }

        try {
            return new ResolvedLink($this->routes[$reference], $this->urls->generate($reference));
        } catch (ExceptionInterface) {
            return null;
        }
    }
}
