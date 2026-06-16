<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TinyBlocks\HttpHealthCheck\DoctrineHealthCheck;

final class DoctrineHealthCheckTest extends TestCase
{
    public function testFromThenNameIsNull(): void
    {
        /** @Given a DBAL connection */
        $connection = $this->createStub(Connection::class);

        /** @When building the check from the connection */
        $name = DoctrineHealthCheck::from(connection: $connection)->name();

        /** @Then the name is null */
        self::assertNull($name);
    }

    public function testFromThenComponentIsDatabase(): void
    {
        /** @Given a DBAL connection */
        $connection = $this->createStub(Connection::class);

        /** @When building the check from the connection */
        $component = DoctrineHealthCheck::from(connection: $connection)->component();

        /** @Then the component is database */
        self::assertSame('database', $component);
    }

    public function testWithNameThenReflectsGivenName(): void
    {
        /** @Given a DBAL connection */
        $connection = $this->createStub(Connection::class);

        /** @When setting a discriminator name */
        $name = DoctrineHealthCheck::from(connection: $connection)->withName('primary')->name();

        /** @Then the name reflects the given value */
        self::assertSame('primary', $name);
    }

    public function testWithQueryWhenCheckingThenUsesGivenQuery(): void
    {
        /** @Given a database platform exposing a dummy select */
        $platform = $this->createStub(AbstractPlatform::class);

        /** @And the platform returns its dummy select */
        $platform->method('getDummySelectSQL')->willReturn('SELECT 1');

        /** @And a DBAL connection backed by that platform */
        $connection = $this->createMock(Connection::class);

        /** @And the connection resolves the platform */
        $connection->method('getDatabasePlatform')->willReturn($platform);

        /** @And the connection expects the custom probe query, not the platform default */
        $connection->expects(self::once())->method('executeQuery')->with('SELECT version()');

        /** @When checking the database with a custom query */
        $health = DoctrineHealthCheck::from(connection: $connection)->withQuery('SELECT version()')->check();

        /** @Then the health is up */
        self::assertTrue($health->isUp());
    }

    public function testCheckWhenDefaultProbeThenIssuesPlatformDummySelect(): void
    {
        /** @Given a database platform exposing a dummy select */
        $platform = $this->createStub(AbstractPlatform::class);

        /** @And the platform returns its dummy select */
        $platform->method('getDummySelectSQL')->willReturn('SELECT 1 AS health');

        /** @And a DBAL connection backed by that platform */
        $connection = $this->createMock(Connection::class);

        /** @And the connection resolves the platform */
        $connection->method('getDatabasePlatform')->willReturn($platform);

        /** @And the connection expects the platform dummy select as the probe */
        $connection->expects(self::once())->method('executeQuery')->with('SELECT 1 AS health');

        /** @When checking the database with the default probe */
        $health = DoctrineHealthCheck::from(connection: $connection)->check();

        /** @Then the health is up */
        self::assertTrue($health->isUp());
    }

    public function testCheckWhenConnectionThrowsThenHealthIsDownWithExceptionMessage(): void
    {
        /** @Given a database platform exposing a dummy select */
        $platform = $this->createStub(AbstractPlatform::class);

        /** @And the platform returns its dummy select */
        $platform->method('getDummySelectSQL')->willReturn('SELECT 1');

        /** @And a DBAL connection backed by that platform */
        $connection = $this->createStub(Connection::class);

        /** @And the connection resolves the platform */
        $connection->method('getDatabasePlatform')->willReturn($platform);

        /** @And the connection throws on the probe query */
        $connection->method('executeQuery')->willThrowException(new RuntimeException(message: 'database is down'));

        /** @When checking the database */
        $health = DoctrineHealthCheck::from(connection: $connection)->check();

        /** @Then the health is down */
        self::assertTrue($health->isDown());

        /** @And the detail is the exception message */
        self::assertSame('database is down', $health->detail);
    }
}
