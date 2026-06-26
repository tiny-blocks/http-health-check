<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck\Internal;

use TinyBlocks\HttpHealthCheck\Dependency;
use TinyBlocks\HttpHealthCheck\DependencyHealth;
use TinyBlocks\HttpHealthCheck\DependencyHealths;
use TinyBlocks\HttpHealthCheck\HealthChecks;
use TinyBlocks\HttpHealthCheck\HealthReport;
use TinyBlocks\Time\MonotonicClock;
use TinyBlocks\Time\SystemMonotonicClock;

final readonly class Assessment
{
    private function __construct(private MonotonicClock $clock, private HealthChecks $checks)
    {
    }

    public static function of(HealthChecks $checks, MonotonicClock $clock = new SystemMonotonicClock()): Assessment
    {
        return new Assessment(clock: $clock, checks: $checks);
    }

    public function run(): HealthReport
    {
        $dependencies = $this->checks->map(
            fn(Dependency $dependency): DependencyHealth => $dependency->measure(clock: $this->clock)
        );

        return HealthReport::from(dependencies: DependencyHealths::createFrom(elements: $dependencies));
    }
}
