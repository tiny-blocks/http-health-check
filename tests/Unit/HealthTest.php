<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\Health;
use TinyBlocks\HttpHealthCheck\Status;

final class HealthTest extends TestCase
{
    public function testUpWhenDetailGivenThenCarriesDetail(): void
    {
        /** @When building an UP health with a detail */
        $health = Health::up(detail: 'all good');

        /** @Then it carries the detail */
        self::assertSame('all good', $health->detail);
    }

    public function testDownWhenDetailGivenThenCarriesDetail(): void
    {
        /** @When building a DOWN health with a detail */
        $health = Health::down(detail: 'connection refused');

        /** @Then it carries the detail */
        self::assertSame('connection refused', $health->detail);
    }

    public function testUpWhenNoDetailThenStatusIsUpWithoutDetail(): void
    {
        /** @When building an UP health without a detail */
        $health = Health::up();

        /** @Then the status is UP */
        self::assertSame(Status::UP, $health->status);

        /** @And there is no detail */
        self::assertNull($health->detail);

        /** @And the outcome reports up */
        self::assertTrue($health->isUp());

        /** @And the outcome does not report down */
        self::assertFalse($health->isDown());
    }

    public function testDownWhenNoDetailThenStatusIsDownWithoutDetail(): void
    {
        /** @When building a DOWN health without a detail */
        $health = Health::down();

        /** @Then the status is DOWN */
        self::assertSame(Status::DOWN, $health->status);

        /** @And there is no detail */
        self::assertNull($health->detail);

        /** @And the outcome reports down */
        self::assertTrue($health->isDown());

        /** @And the outcome does not report up */
        self::assertFalse($health->isUp());
    }
}
