# ddd-menu-bundle

The menus of a site — its main menu, the columns of its footer — as a
reusable DDD Bounded Context for Symfony: composed in a Sylius back office
from the destinations the application offers, printed from Twig.

## Installation

```bash
composer require alexandrebulete/ddd-menu-bundle
bin/console doctrine:migrations:migrate
```

The Flex recipe registers the bundle. The `menu` table comes with the
bundle's own migration.

## Model

| Aggregate | Holds |
|---|---|
| `Menu` | zone, title, position in its zone, ordered links, created and last updated |

- A **zone** is where a template prints menus (`main`, `footer`). The
  application declares them; a **single** zone holds one menu (a main
  menu), the others as many as needed (footer columns, by position).
- A **link** points to a destination the application offers (`route:home`,
  `page:<id>`…) or to an address typed in (`https://…`, `mailto:…`, or a path
  such as `/about#contact`). Only the destination is stored, never its
  resolved URL: a page that changes address keeps its links.
- Without a label, a link shows its destination's own name. A link to an
  address needs one.
- A link whose destination is gone or not public is left out when printed,
  without an error.

## Use cases

Commands: `CreateMenu`, `RenameMenu`, `MoveMenu` (zone, position),
`ChangeMenuItems` (replaces the links: adding, removing and reordering are
one intent), `DeleteMenu`.

Queries: `FindMenus`, `FindMenu` (back office), `FindZoneMenus`
(`#[PublicAccess]`).

## Configuration

```yaml
menu:
    admin:
        enabled: true          # Sylius screens; false for a headless setup
    zones:
        main: { label: Main menu, single: true }
        footer: Footer
    routes:                    # the site's own pages, routes without parameters
        home: Home
        blog: Blog
```

Labels are translated in the `messages` domain.

## Printing menus

```twig
{# a bar: every link of the zone #}
{% for link in menu_links('main') %}
    <a href="{{ link.url }}"{% if link.external %} rel="noopener"{% endif %}>{{ link.label }}</a>
{% endfor %}

{# columns: one per menu, with its title #}
{% for menu in menus('footer') %}
    <nav aria-label="{{ menu.title }}">
        <span>{{ menu.title }}</span>
        {% for link in menu.links %}<a href="{{ link.url }}">{{ link.label }}</a>{% endfor %}
    </nav>
{% endfor %}
```

Each zone is asked once per request.

## Other destinations

A link source offers destinations in the back office and resolves them when
printed. Implement `LinkSourceInterface` (autoconfigured) — for instance
the pages of another bundle:

```php
final readonly class PageLinkSource implements LinkSourceInterface
{
    public function kind(): string { return 'page'; }
    public function label(): string { return 'Pages'; }
    public function choices(): array { /* title => id */ }
    public function resolve(string $reference): ?ResolvedLink { /* null when offline */ }
}
```

Return `null` from `resolve()` for anything a visitor must not reach: the
link is then left out.

## Seams

| Port | Default | Rebind for |
|---|---|---|
| `DomainEventPublisherInterface` | `ImmediateEventPublisher` (in-process, in the transaction) | an outbox |
| `MenuRepositoryInterface` | Doctrine | another storage |
| `ZoneCatalogInterface` | `ConfiguredZoneCatalog` (`menu.zones`) | zones stored elsewhere |

## Development

```bash
composer qa    # phpstan (max, strict) + deptrac + phpunit
```

Integration tests run on SQLite in memory, or on the database given by
`DDD_TEST_DATABASE_URL` (CI: PostgreSQL, MySQL, SQLite).

## License

MIT
