<?php

declare(strict_types=1);

namespace Test\TinyBlocks\HttpHealthCheck\Unit;

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\MockHandler;
use Aws\Result;
use Aws\Ssm\SsmClient;
use PHPUnit\Framework\TestCase;
use TinyBlocks\HttpHealthCheck\SsmHealthCheck;

final class SsmHealthCheckTest extends TestCase
{
    private MockHandler $handler;
    private SsmClient $client;

    protected function setUp(): void
    {
        $this->handler = new MockHandler();
        $this->client = new SsmClient([
            'region'      => 'us-east-1',
            'version'     => 'latest',
            'retries'     => 0,
            'handler'     => $this->handler,
            'credentials' => ['key' => 'key', 'secret' => 'secret']
        ]);
    }

    public function testFromThenNameIsNull(): void
    {
        /** @When building the check from the client */
        $name = SsmHealthCheck::from(client: $this->client)->name();

        /** @Then the name is null */
        self::assertNull($name);
    }

    public function testFromThenComponentIsSsm(): void
    {
        /** @When building the check from the client */
        $component = SsmHealthCheck::from(client: $this->client)->component();

        /** @Then the component is ssm */
        self::assertSame('ssm', $component);
    }

    public function testWithNameThenReflectsGivenName(): void
    {
        /** @When setting a discriminator name */
        $name = SsmHealthCheck::from(client: $this->client)->withName('primary')->name();

        /** @Then the name reflects the given value */
        self::assertSame('primary', $name);
    }

    public function testCheckWhenDescribeSucceedsThenHealthIsUp(): void
    {
        /** @Given the SSM client returns a successful describe result */
        $this->handler->append(new Result());

        /** @When checking the SSM dependency */
        $health = SsmHealthCheck::from(client: $this->client)->check();

        /** @Then the health is up */
        self::assertTrue($health->isUp());
    }

    public function testCheckWhenDescribeSucceedsThenProbesWithBoundedRequest(): void
    {
        /** @Given the SSM client returns a successful describe result */
        $this->handler->append(new Result());

        /** @When checking the SSM dependency */
        SsmHealthCheck::from(client: $this->client)->check();

        /** @Then the probe requests a single parameter */
        self::assertSame(1, $this->handler->getLastCommand()['MaxResults']);
    }

    public function testCheckWhenDescribeFailsThenHealthIsDownWithExceptionMessage(): void
    {
        /** @Given the SSM client rejects the describe call */
        $this->handler->append(static function (CommandInterface $command): AwsException {
            return new AwsException(message: 'ssm is unavailable', command: $command);
        });

        /** @When checking the SSM dependency */
        $health = SsmHealthCheck::from(client: $this->client)->check();

        /** @Then the health is down */
        self::assertTrue($health->isDown());

        /** @And the detail is the exception message */
        self::assertSame('ssm is unavailable', $health->detail);
    }
}
