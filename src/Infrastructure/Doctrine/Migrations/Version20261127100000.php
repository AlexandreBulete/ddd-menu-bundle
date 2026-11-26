<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the menus.
 *
 * DBAL's built-in types only, never the bundle's own: a migration is a
 * snapshot. The integration tests check that the result is the mapped schema
 * on PostgreSQL, MySQL and SQLite.
 */
final class Version20261127100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Menu: the menus of a site and their links.';
    }

    public function up(Schema $schema): void
    {
        $menu = $schema->createTable('menu');
        $menu->addColumn('id', Types::GUID);
        $menu->addColumn('zone', Types::STRING, ['length' => 50]);
        $menu->addColumn('title', Types::STRING, ['length' => 100]);
        $menu->addColumn('position', Types::SMALLINT);
        $menu->addColumn('items', Types::JSONB);
        $menu->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $menu->addColumn('updated_at', Types::DATETIME_IMMUTABLE);
        $menu->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $menu->addIndex(['zone', 'position'], 'idx_menu_zone');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('menu');
    }
}
