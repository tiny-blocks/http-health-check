<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

/**
 * Aggregate outcome of running a {@see HealthChecks} set: the overall {@see Readiness} and the per-dependency results.
 */
final readonly class HealthReport
{
    private function __construct(private DependencyHealths $dependencies)
    {
    }

    /**
     * Creates a HealthReport from the measured per-dependency results.
     *
     * @param DependencyHealths $dependencies The measured results.
     * @return HealthReport The report.
     */
    public static function from(DependencyHealths $dependencies): HealthReport
    {
        return new HealthReport(dependencies: $dependencies);
    }

    /**
     * Returns the HealthReport as an associative array.
     *
     * @return array<string, mixed> The per-check results and the overall status.
     */
    public function toArray(): array
    {
        $checks = $this->dependencies
            ->map(static fn(DependencyHealth $dependency): array => $dependency->toArray())
            ->toArray();

        return [
            'checks' => $checks,
            'status' => $this->dependencies->readiness()->value
        ];
    }

    /**
     * Returns the overall readiness.
     *
     * @return Readiness The aggregate readiness.
     */
    public function readiness(): Readiness
    {
        return $this->dependencies->readiness();
    }
}
