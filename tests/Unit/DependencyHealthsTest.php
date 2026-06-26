<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\DependencyHealth;
use TinyBlocks\HttpHealthCheck\DependencyHealths;
use TinyBlocks\HttpHealthCheck\Health;
use TinyBlocks\HttpHealthCheck\Readiness;

final class DependencyHealthsTest extends TestCase
{
    public function testReadinessWhenEmptyThenHealthy(): void
    {
        /** @Given an empty set of dependency results */
        $dependencies = DependencyHealths::createFromEmpty();

        /** @When reducing it to a readiness */
        $readiness = $dependencies->readiness();

        /** @Then the readiness is healthy */
        self::assertSame(Readiness::HEALTHY, $readiness);
    }

    public function testReadinessWhenEveryCheckIsUpThenHealthy(): void
    {
        /** @Given a healthy critical dependency result */
        $critical = new DependencyHealth(
            name: null,
            health: Health::up(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 0.0
        );

        /** @And a healthy optional dependency result */
        $optional = new DependencyHealth(
            name: null,
            health: Health::up(),
            critical: false,
            component: 'cache',
            durationInMilliseconds: 0.0
        );

        /** @When reducing them to a readiness */
        $readiness = DependencyHealths::createFrom(elements: [$critical, $optional])->readiness();

        /** @Then the readiness is healthy */
        self::assertSame(Readiness::HEALTHY, $readiness);
    }

    public function testReadinessWhenOptionalCheckIsDownThenDegraded(): void
    {
        /** @Given a healthy critical dependency result */
        $critical = new DependencyHealth(
            name: null,
            health: Health::up(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 0.0
        );

        /** @And a failing optional dependency result */
        $optional = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: false,
            component: 'cache',
            durationInMilliseconds: 0.0
        );

        /** @When reducing them to a readiness */
        $readiness = DependencyHealths::createFrom(elements: [$critical, $optional])->readiness();

        /** @Then the readiness is degraded */
        self::assertSame(Readiness::DEGRADED, $readiness);
    }

    public function testReadinessWhenCriticalCheckIsDownThenUnavailable(): void
    {
        /** @Given a failing critical dependency result */
        $critical = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 0.0
        );

        /** @When reducing it to a readiness */
        $readiness = DependencyHealths::createFrom(elements: [$critical])->readiness();

        /** @Then the readiness is unavailable */
        self::assertSame(Readiness::UNAVAILABLE, $readiness);
    }

    public function testReadinessWhenCriticalAndOptionalAreDownThenUnavailable(): void
    {
        /** @Given a failing critical dependency result */
        $critical = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 0.0
        );

        /** @And a failing optional dependency result */
        $optional = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: false,
            component: 'cache',
            durationInMilliseconds: 0.0
        );

        /** @When reducing them to a readiness */
        $readiness = DependencyHealths::createFrom(elements: [$critical, $optional])->readiness();

        /** @Then the readiness is unavailable because the critical failure wins */
        self::assertSame(Readiness::UNAVAILABLE, $readiness);
    }
}
