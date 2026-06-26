<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

use Doctrine\DBAL\Connection;
use Throwable;

/**
 * {@see HealthCheck} that verifies database availability through a Doctrine DBAL connection.
 *
 * <p>The default probe is the platform's own dummy select, resolved from the connection, so the
 * check works on any DBAL platform without assuming a specific database. A custom probe query can
 * be supplied with {@see withQuery()}.</p>
 */
final readonly class DoctrineHealthCheck implements HealthCheck
{
    private function __construct(private ?string $name, private ?string $query, private Connection $connection)
    {
    }

    /**
     * Creates a DoctrineHealthCheck probing the given connection with the platform default select.
     *
     * @param Connection $connection The DBAL connection used to check the database.
     * @return DoctrineHealthCheck The check.
     */
    public static function from(Connection $connection): DoctrineHealthCheck
    {
        return new DoctrineHealthCheck(name: null, query: null, connection: $connection);
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function check(): Health
    {
        try {
            $query = $this->query ?? $this->connection->getDatabasePlatform()->getDummySelectSQL();
            $this->connection->executeQuery($query);

            return Health::up();
        } catch (Throwable $exception) {
            return Health::down(detail: $exception->getMessage());
        }
    }

    /**
     * Returns a copy of the DoctrineHealthCheck with the discriminator name set.
     *
     * @param string $name The discriminator distinguishing this check in the report.
     * @return DoctrineHealthCheck A new check carrying the given name.
     */
    public function withName(string $name): DoctrineHealthCheck
    {
        return new DoctrineHealthCheck(name: $name, query: $this->query, connection: $this->connection);
    }

    public function component(): string
    {
        return 'database';
    }

    /**
     * Returns a copy of the DoctrineHealthCheck with the probe query replaced.
     *
     * @param string $query The SQL query issued to verify availability.
     * @return DoctrineHealthCheck A new check using the given query.
     */
    public function withQuery(string $query): DoctrineHealthCheck
    {
        return new DoctrineHealthCheck(name: $this->name, query: $query, connection: $this->connection);
    }
}
