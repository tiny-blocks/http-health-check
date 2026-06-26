<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use TinyBlocks\Time\MonotonicClock;

final class MonotonicClockFake implements MonotonicClock
{
    private int $reads = 0;

    private function __construct(private readonly int $step)
    {
    }

    public static function withElapsedInNanoseconds(int $nanoseconds): MonotonicClockFake
    {
        return new MonotonicClockFake(step: $nanoseconds);
    }

    public function nanoseconds(): int
    {
        $reading = $this->reads * $this->step;
        $this->reads++;

        return $reading;
    }
}
