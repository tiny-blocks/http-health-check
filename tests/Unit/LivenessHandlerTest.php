<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\LivenessHandler;

final class LivenessHandlerTest extends TestCase
{
    public function testHandleThenRespondsOkWithUpStatus(): void
    {
        /** @Given a server request to the liveness endpoint */
        $request = new ServerRequest(method: 'GET', uri: '/health/live');

        /** @When handling the request */
        $response = LivenessHandler::create()->handle($request);

        /** @Then the response status code is 200 */
        self::assertSame(200, $response->getStatusCode());

        /** @And the body reports the process is up */
        self::assertJsonStringEqualsJsonString('{"status":"UP"}', (string) $response->getBody());
    }
}
