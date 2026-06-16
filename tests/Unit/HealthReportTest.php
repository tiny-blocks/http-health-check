<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\DependencyHealth;
use TinyBlocks\HttpHealthCheck\DependencyHealths;
use TinyBlocks\HttpHealthCheck\Health;
use TinyBlocks\HttpHealthCheck\HealthReport;
use TinyBlocks\HttpHealthCheck\Readiness;

final class HealthReportTest extends TestCase
{
    public function testToArrayThenReturnsStatusAndPerCheckResults(): void
    {
        /** @Given a healthy critical dependency result */
        $first = new DependencyHealth(
            name: 'primary',
            health: Health::up(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 5.0
        );

        /** @And a failing optional dependency result */
        $second = new DependencyHealth(
            name: null,
            health: Health::down(detail: 'unreachable'),
            critical: false,
            component: 'cache',
            durationInMilliseconds: 2.5
        );

        /** @And a report built from both results */
        $report = HealthReport::from(dependencies: DependencyHealths::createFrom(elements: [$first, $second]));

        /** @When converting the report to an array */
        $result = $report->toArray();

        /** @Then it exposes the per-check results and the overall status */
        self::assertSame([
            'checks' => [
                [
                    'name'                     => 'primary',
                    'status'                   => 'UP',
                    'critical'                 => true,
                    'component'                => 'database',
                    'duration_in_milliseconds' => 5.0
                ],
                [
                    'detail'                   => 'unreachable',
                    'status'                   => 'DOWN',
                    'critical'                 => false,
                    'component'                => 'cache',
                    'duration_in_milliseconds' => 2.5
                ]
            ],
            'status' => 'DEGRADED'
        ], $result);
    }

    public function testReadinessWhenCriticalIsDownThenDelegatesToUnavailable(): void
    {
        /** @Given a failing critical dependency result */
        $critical = new DependencyHealth(
            name: null,
            health: Health::down(),
            critical: true,
            component: 'database',
            durationInMilliseconds: 0.0
        );

        /** @And a report built from it */
        $report = HealthReport::from(dependencies: DependencyHealths::createFrom(elements: [$critical]));

        /** @When asking for the overall readiness */
        $readiness = $report->readiness();

        /** @Then the readiness is unavailable */
        self::assertSame(Readiness::UNAVAILABLE, $readiness);
    }
}
