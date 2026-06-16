<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\Health;
use TinyBlocks\HttpHealthCheck\HealthChecks;
use TinyBlocks\HttpHealthCheck\Readiness;

final class HealthChecksTest extends TestCase
{
    public function testReportWhenNoChecksThenReadinessIsHealthy(): void
    {
        /** @Given an empty set of health checks */
        $checks = HealthChecks::createFromEmpty();

        /** @When reporting the aggregate health */
        $readiness = $checks->report()->readiness();

        /** @Then the readiness is healthy */
        self::assertSame(Readiness::HEALTHY, $readiness);
    }

    public function testWithCriticalWhenRegisteringThenNewSetHasOneCheck(): void
    {
        /** @Given an empty set of health checks */
        $checks = HealthChecks::createFromEmpty();

        /** @When registering a critical check */
        $updated = $checks->withCritical(check: HealthCheckFake::withHealth(health: Health::up()));

        /** @Then the new set has one registration */
        self::assertCount(1, $updated);
    }

    public function testWithOptionalWhenRegisteringThenNewSetHasOneCheck(): void
    {
        /** @Given an empty set of health checks */
        $checks = HealthChecks::createFromEmpty();

        /** @When registering an optional check */
        $updated = $checks->withOptional(check: HealthCheckFake::withHealth(health: Health::up()));

        /** @Then the new set has one registration */
        self::assertCount(1, $updated);
    }

    public function testWithCriticalWhenRegisteringThenReceiverStaysEmpty(): void
    {
        /** @Given an empty set of health checks */
        $checks = HealthChecks::createFromEmpty();

        /** @When registering a critical check */
        $checks->withCritical(check: HealthCheckFake::withHealth(health: Health::up()));

        /** @Then the original set remains empty */
        self::assertTrue($checks->isEmpty());
    }

    public function testWithOptionalWhenRegisteringThenReceiverStaysEmpty(): void
    {
        /** @Given an empty set of health checks */
        $checks = HealthChecks::createFromEmpty();

        /** @When registering an optional check */
        $checks->withOptional(check: HealthCheckFake::withHealth(health: Health::up()));

        /** @Then the original set remains empty */
        self::assertTrue($checks->isEmpty());
    }

    public function testReportWhenOptionalCheckIsDownThenReadinessIsDegraded(): void
    {
        /** @Given a set registering a failing optional check */
        $checks = HealthChecks::createFromEmpty()
            ->withOptional(check: HealthCheckFake::withHealth(health: Health::down()));

        /** @When reporting the aggregate health */
        $readiness = $checks->report()->readiness();

        /** @Then the readiness is degraded */
        self::assertSame(Readiness::DEGRADED, $readiness);
    }

    public function testReportWhenCriticalCheckIsDownThenReadinessIsUnavailable(): void
    {
        /** @Given a set registering a failing critical check */
        $checks = HealthChecks::createFromEmpty()
            ->withCritical(check: HealthCheckFake::withHealth(health: Health::down()));

        /** @When reporting the aggregate health */
        $readiness = $checks->report()->readiness();

        /** @Then the readiness is unavailable */
        self::assertSame(Readiness::UNAVAILABLE, $readiness);
    }
}
