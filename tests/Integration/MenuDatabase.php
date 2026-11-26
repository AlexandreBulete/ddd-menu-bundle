<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Tests\Integration;

use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Migrations\Version20261127100000;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type\MenuIdType;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type\MenuItemsType;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type\ZoneType;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\ORMSetup;
use Psr\Log\NullLogger;

/**
 * A database migrated the way an application migrates it: SQLite in memory,
 * or the one given by DDD_TEST_DATABASE_URL.
 */
final class MenuDatabase
{
    public static function migrated(): EntityManagerInterface
    {
        foreach ([MenuIdType::NAME => MenuIdType::class, ZoneType::NAME => ZoneType::class, MenuItemsType::NAME => MenuItemsType::class] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }

        $config = ORMSetup::createConfiguration(true);
        $config->setMetadataDriverImpl(new SimplifiedXmlDriver([
            dirname(__DIR__, 2) . '/src/Infrastructure/Doctrine/Mapping' => 'AlexandreBulete\DddMenuBundle\Domain\Model',
        ]));
        $config->enableNativeLazyObjects(true);
        $config->setSchemaAssetsFilter(static fn (string $asset): bool => $asset === 'menu');

        $connection = self::connect($config);
        $schemaManager = $connection->createSchemaManager();
        foreach ($schemaManager->listTableNames() as $table) {
            $schemaManager->dropTable($table);
        }

        // What `doctrine:migrations:migrate` does for a Schema API migration.
        foreach ([Version20261127100000::class] as $class) {
            $migration = new $class($connection, new NullLogger());
            $from = $schemaManager->introspectSchema();
            $to = clone $from;
            $migration->up($to);
            foreach ($connection->getDatabasePlatform()->getAlterSchemaSQL($schemaManager->createComparator()->compareSchemas($from, $to)) as $sql) {
                $connection->executeStatement($sql);
            }
        }

        return new EntityManager($connection, $config);
    }

    private static function connect(Configuration $config): Connection
    {
        $url = getenv('DDD_TEST_DATABASE_URL');
        $params = (new DsnParser([
            'pdo-sqlite' => 'pdo_sqlite',
            'postgresql' => 'pdo_pgsql',
            'mysql' => 'pdo_mysql',
        ]))->parse(is_string($url) && $url !== '' ? $url : 'pdo-sqlite:///:memory:');

        return DriverManager::getConnection($params, $config);
    }
}
