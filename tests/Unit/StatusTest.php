<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\Status;

final class StatusTest extends TestCase
{
    public function testIsUpWhenStateIsUpThenReturnsTrue(): void
    {
        /** @Given the UP status */
        $status = Status::UP;

        /** @When checking whether the state is up */
        $result = $status->isUp();

        /** @Then it returns true */
        self::assertTrue($result);
    }

    public function testIsDownWhenStateIsUpThenReturnsFalse(): void
    {
        /** @Given the UP status */
        $status = Status::UP;

        /** @When checking whether the state is down */
        $result = $status->isDown();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testIsUpWhenStateIsDownThenReturnsFalse(): void
    {
        /** @Given the DOWN status */
        $status = Status::DOWN;

        /** @When checking whether the state is up */
        $result = $status->isUp();

        /** @Then it returns false */
        self::assertFalse($result);
    }

    public function testIsDownWhenStateIsDownThenReturnsTrue(): void
    {
        /** @Given the DOWN status */
        $status = Status::DOWN;

        /** @When checking whether the state is down */
        $result = $status->isDown();

        /** @Then it returns true */
        self::assertTrue($result);
    }
}
