<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\DrainMarker;
use TinyBlocks\HttpHealthCheck\Health;
use TinyBlocks\HttpHealthCheck\HealthChecks;
use TinyBlocks\HttpHealthCheck\ReadinessHandler;

final class ReadinessHandlerTest extends TestCase
{
    private string $markerFile = '';

    protected function tearDown(): void
    {
        if ($this->markerFile !== '' && file_exists($this->markerFile)) {
            unlink($this->markerFile);
        }
    }

    public function testHandleWhenHealthyThenRespondsOk(): void
    {
        /** @Given a server request to the readiness endpoint */
        $request = new ServerRequest(method: 'GET', uri: '/health/ready');

        /** @And a readiness handler with a healthy critical check */
        $handler = ReadinessHandler::from(
            checks: HealthChecks::createFromEmpty()
                ->withCritical(check: HealthCheckFake::withHealth(health: Health::up()))
        );

        /** @When handling the request */
        $response = $handler->handle($request);

        /** @Then the response status code is 200 */
        self::assertSame(200, $response->getStatusCode());

        /** @And the body reports a healthy readiness */
        self::assertStringContainsString('"status":"HEALTHY"', (string) $response->getBody());
    }

    public function testHandleWhenDegradedThenRespondsOk(): void
    {
        /** @Given a server request to the readiness endpoint */
        $request = new ServerRequest(method: 'GET', uri: '/health/ready');

        /** @And a readiness handler with a healthy critical check and a failing optional check */
        $handler = ReadinessHandler::from(
            checks: HealthChecks::createFromEmpty()
                ->withCritical(check: HealthCheckFake::withHealth(health: Health::up()))
                ->withOptional(check: HealthCheckFake::withHealth(health: Health::down()))
        );

        /** @When handling the request */
        $response = $handler->handle($request);

        /** @Then the response status code is 200 */
        self::assertSame(200, $response->getStatusCode());

        /** @And the body reports a degraded readiness */
        self::assertStringContainsString('"status":"DEGRADED"', (string) $response->getBody());
    }

    public function testHandleWhenUnavailableThenRespondsServiceUnavailable(): void
    {
        /** @Given a server request to the readiness endpoint */
        $request = new ServerRequest(method: 'GET', uri: '/health/ready');

        /** @And a readiness handler with a failing critical check */
        $handler = ReadinessHandler::from(
            checks: HealthChecks::createFromEmpty()
                ->withCritical(check: HealthCheckFake::withHealth(health: Health::down()))
        );

        /** @When handling the request */
        $response = $handler->handle($request);

        /** @Then the response status code is 503 */
        self::assertSame(503, $response->getStatusCode());

        /** @And the body reports an unavailable readiness */
        self::assertStringContainsString('"status":"UNAVAILABLE"', (string) $response->getBody());
    }

    public function testHandleWhenDrainMarkerPresentButNotDrainingThenRunsChecks(): void
    {
        /** @Given a server request to the readiness endpoint */
        $request = new ServerRequest(method: 'GET', uri: '/health/ready');

        /** @And a temporary drain marker file */
        $path = (string) tempnam(sys_get_temp_dir(), 'drain');

        /** @And the drain marker file is removed */
        unlink($path);

        /** @And a readiness handler with a healthy critical check and an absent drain marker */
        $handler = ReadinessHandler::from(
            checks: HealthChecks::createFromEmpty()
                ->withCritical(check: HealthCheckFake::withHealth(health: Health::up())),
            drainMarker: DrainMarker::from(path: $path)
        );

        /** @When handling the request */
        $response = $handler->handle($request);

        /** @Then the response status code is 200 */
        self::assertSame(200, $response->getStatusCode());

        /** @And the body reports a healthy readiness */
        self::assertStringContainsString('"status":"HEALTHY"', (string) $response->getBody());
    }

    public function testHandleWhenDrainingThenRespondsServiceUnavailableWithoutRunningChecks(): void
    {
        /** @Given a server request to the readiness endpoint */
        $request = new ServerRequest(method: 'GET', uri: '/health/ready');

        /** @And an existing drain marker file */
        $this->markerFile = (string) tempnam(sys_get_temp_dir(), 'drain');

        /** @And a draining readiness handler with a healthy critical check */
        $handler = ReadinessHandler::from(
            checks: HealthChecks::createFromEmpty()
                ->withCritical(check: HealthCheckFake::withHealth(health: Health::up())),
            drainMarker: DrainMarker::from(path: $this->markerFile)
        );

        /** @When handling the request */
        $response = $handler->handle($request);

        /** @Then the response status code is 503 */
        self::assertSame(503, $response->getStatusCode());

        /** @And the body reports unavailable without any per-check results */
        self::assertJsonStringEqualsJsonString('{"status":"UNAVAILABLE"}', (string) $response->getBody());
    }
}
