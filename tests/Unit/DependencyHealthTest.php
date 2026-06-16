<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\DependencyHealth;
use TinyBlocks\HttpHealthCheck\Health;

final class DependencyHealthTest extends TestCase
{
    public function testIsCriticalFailureWhenCriticalAndUpThenReturnsFalse(): void
    {
        /** @Given a critical dependency reporting up */
        $dependency = new DependencyHealth(
            name: null,
            health: Health::up(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 0.0
        );

        /** @When checking whether it is a critical failure */
        $result = $dependency->isCriticalFailure();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testIsOptionalFailureWhenOptionalAndUpThenReturnsFalse(): void
    {
        /** @Given an optional dependency reporting up */
        $dependency = new DependencyHealth(
            name: null,
            health: Health::up(),
            critical: false,
            component: 'cache',
            durationInMilliseconds: 0.0
        );

        /** @When checking whether it is an optional failure */
        $result = $dependency->isOptionalFailure();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testIsCriticalFailureWhenCriticalAndDownThenReturnsTrue(): void
    {
        /** @Given a critical dependency reporting down */
        $dependency = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 0.0
        );

        /** @When checking whether it is a critical failure */
        $result = $dependency->isCriticalFailure();

        /** @Then it returns true */
        self::assertTrue($result);
    }

    public function testIsOptionalFailureWhenOptionalAndDownThenReturnsTrue(): void
    {
        /** @Given an optional dependency reporting down */
        $dependency = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: false,
            component: 'cache',
            durationInMilliseconds: 0.0
        );

        /** @When checking whether it is an optional failure */
        $result = $dependency->isOptionalFailure();

        /** @Then it returns true */
        self::assertTrue($result);
    }

    public function testIsCriticalFailureWhenOptionalAndDownThenReturnsFalse(): void
    {
        /** @Given an optional dependency reporting down */
        $dependency = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: false,
            component: 'cache',
            durationInMilliseconds: 0.0
        );

        /** @When checking whether it is a critical failure */
        $result = $dependency->isCriticalFailure();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testIsOptionalFailureWhenCriticalAndDownThenReturnsFalse(): void
    {
        /** @Given a critical dependency reporting down */
        $dependency = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 0.0
        );

        /** @When checking whether it is an optional failure */
        $result = $dependency->isOptionalFailure();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testToArrayWhenNameAndDetailAbsentThenOmitsOptionalFields(): void
    {
        /** @Given a measured dependency without a name or a detail */
        $dependency = new DependencyHealth(
            name: null,
            health: Health::up(),
            critical: false,
            component: 'cache',
            durationInMilliseconds: 2.5
        );

        /** @When converting it to an array */
        $result = $dependency->toArray();

        /** @Then it omits the optional name and detail fields */
        self::assertSame([
            'status'                   => 'UP',
            'critical'                 => false,
            'component'                => 'cache',
            'duration_in_milliseconds' => 2.5
        ], $result);
    }

    public function testToArrayWhenNameAndDetailPresentThenIncludesEveryField(): void
    {
        /** @Given a measured dependency with a name and a detail */
        $dependency = new DependencyHealth(
            name: 'primary',
            health: Health::down(detail: 'connection refused'),
            critical: true,
            component: 'database',
            durationInMilliseconds: 5.0
        );

        /** @When converting it to an array */
        $result = $dependency->toArray();

        /** @Then it includes every field */
        self::assertSame([
            'name'                     => 'primary',
            'detail'                   => 'connection refused',
            'status'                   => 'DOWN',
            'critical'                 => true,
            'component'                => 'database',
            'duration_in_milliseconds' => 5.0
        ], $result);
    }
}
