<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\Readiness;

final class ReadinessTest extends TestCase
{
    public function testIsHealthyWhenReadinessIsHealthyThenReturnsTrue(): void
    {
        /** @Given a healthy readiness */
        $readiness = Readiness::HEALTHY;

        /** @When checking whether the readiness is healthy */
        $result = $readiness->isHealthy();

        /** @Then it returns true */
        self::assertTrue($result);
    }

    public function testIsDegradedWhenReadinessIsDegradedThenReturnsTrue(): void
    {
        /** @Given a degraded readiness */
        $readiness = Readiness::DEGRADED;

        /** @When checking whether the readiness is degraded */
        $result = $readiness->isDegraded();

        /** @Then it returns true */
        self::assertTrue($result);
    }

    public function testIsDegradedWhenReadinessIsHealthyThenReturnsFalse(): void
    {
        /** @Given a healthy readiness */
        $readiness = Readiness::HEALTHY;

        /** @When checking whether the readiness is degraded */
        $result = $readiness->isDegraded();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testIsHealthyWhenReadinessIsDegradedThenReturnsFalse(): void
    {
        /** @Given a degraded readiness */
        $readiness = Readiness::DEGRADED;

        /** @When checking whether the readiness is healthy */
        $result = $readiness->isHealthy();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testIsUnavailableWhenReadinessIsHealthyThenReturnsFalse(): void
    {
        /** @Given a healthy readiness */
        $readiness = Readiness::HEALTHY;

        /** @When checking whether the readiness is unavailable */
        $result = $readiness->isUnavailable();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testIsUnavailableWhenReadinessIsUnavailableThenReturnsTrue(): void
    {
        /** @Given an unavailable readiness */
        $readiness = Readiness::UNAVAILABLE;

        /** @When checking whether the readiness is unavailable */
        $result = $readiness->isUnavailable();

        /** @Then it returns true */
        self::assertTrue($result);
    }
}
