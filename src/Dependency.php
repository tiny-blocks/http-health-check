<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

use Throwable;
use TinyBlocks\Time\MonotonicClock;
use TinyBlocks\Time\Stopwatch;

/**
 * A service dependency wrapped with its criticality, ready to be measured.
 */
final readonly class Dependency
{
    private function __construct(private HealthCheck $check, private bool $critical)
    {
    }

    /**
     * Creates a critical Dependency whose failure makes the service unavailable.
     *
     * @param HealthCheck $check The check verifying the dependency.
     * @return Dependency The critical dependency.
     */
    public static function critical(HealthCheck $check): Dependency
    {
        return new Dependency(check: $check, critical: true);
    }

    /**
     * Creates an optional Dependency whose failure only degrades the service.
     *
     * @param HealthCheck $check The check verifying the dependency.
     * @return Dependency The optional dependency.
     */
    public static function optional(HealthCheck $check): Dependency
    {
        return new Dependency(check: $check, critical: false);
    }

    /**
     * Runs the wrapped check, timed, and reports the dependency's measured health.
     *
     * @param MonotonicClock $clock The clock used to measure the elapsed time.
     * @return DependencyHealth The measured result for this dependency.
     */
    public function measure(MonotonicClock $clock): DependencyHealth
    {
        $stopwatch = Stopwatch::start(clock: $clock);

        try {
            $health = $this->check->check();
        } catch (Throwable $exception) {
            $health = Health::down(detail: $exception->getMessage());
        }

        return new DependencyHealth(
            name: $this->check->name(),
            health: $health,
            critical: $this->critical,
            component: $this->check->component(),
            durationInMilliseconds: $stopwatch->elapsed()->toMilliseconds()
        );
    }
}
