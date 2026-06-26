<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TinyBlocks\HttpHealthCheck\Dependency;
use TinyBlocks\HttpHealthCheck\Health;

final class DependencyTest extends TestCase
{
    public function testCriticalWhenMeasuredThenCarriesCriticalFlag(): void
    {
        /** @Given a clock reporting a fixed elapsed time */
        $clock = MonotonicClockFake::withElapsedInNanoseconds(nanoseconds: 5_000_000);

        /** @And a critical dependency over a healthy check */
        $dependency = Dependency::critical(check: HealthCheckFake::withHealth(health: Health::up()));

        /** @When measuring the dependency */
        $result = $dependency->measure(clock: $clock);

        /** @Then the result is flagged as critical */
        self::assertTrue($result->critical);
    }

    public function testOptionalWhenMeasuredThenCarriesOptionalFlag(): void
    {
        /** @Given a clock reporting a fixed elapsed time */
        $clock = MonotonicClockFake::withElapsedInNanoseconds(nanoseconds: 5_000_000);

        /** @And an optional dependency over a healthy check */
        $dependency = Dependency::optional(check: HealthCheckFake::withHealth(health: Health::up()));

        /** @When measuring the dependency */
        $result = $dependency->measure(clock: $clock);

        /** @Then the result is not flagged as critical */
        self::assertFalse($result->critical);
    }

    public function testMeasureWhenCheckSucceedsThenReportsCheckMetadata(): void
    {
        /** @Given a clock reporting a fixed elapsed time */
        $clock = MonotonicClockFake::withElapsedInNanoseconds(nanoseconds: 5_000_000);

        /** @And a critical dependency over a named healthy check */
        $dependency = Dependency::critical(
            check: HealthCheckFake::withHealth(health: Health::up(), name: 'primary', component: 'database')
        );

        /** @When measuring the dependency */
        $result = $dependency->measure(clock: $clock);

        /** @Then the result carries the check name */
        self::assertSame('primary', $result->name);

        /** @And the result carries the check component */
        self::assertSame('database', $result->component);

        /** @And the result carries the produced health */
        self::assertTrue($result->health->isUp());

        /** @And the result carries the elapsed duration in milliseconds */
        self::assertSame(5.0, $result->durationInMilliseconds);
    }

    public function testMeasureWhenCheckThrowsThenHealthIsDownWithExceptionMessage(): void
    {
        /** @Given a clock reporting a fixed elapsed time */
        $clock = MonotonicClockFake::withElapsedInNanoseconds(nanoseconds: 5_000_000);

        /** @And a critical dependency over a check that throws */
        $dependency = Dependency::critical(
            check: HealthCheckFake::withFailure(failure: new RuntimeException('connection refused'))
        );

        /** @When measuring the dependency */
        $result = $dependency->measure(clock: $clock);

        /** @Then the health is down */
        self::assertTrue($result->health->isDown());

        /** @And the detail is the exception message */
        self::assertSame('connection refused', $result->health->detail);
    }
}
