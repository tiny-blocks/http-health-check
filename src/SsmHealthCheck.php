<?php

declare(strict_types=1);

namespace TinyBlocks\HttpHealthCheck;

use Aws\Ssm\SsmClient;
use Throwable;

/**
 * {@see HealthCheck} that verifies AWS SSM availability through a lightweight describe call.
 */
final readonly class SsmHealthCheck implements HealthCheck
{
    private function __construct(private ?string $name, private SsmClient $client)
    {
    }

    /**
     * Creates an SsmHealthCheck probing the given SSM client.
     *
     * @param SsmClient $client The SSM client used to check availability.
     * @return SsmHealthCheck The check.
     */
    public static function from(SsmClient $client): SsmHealthCheck
    {
        return new SsmHealthCheck(name: null, client: $client);
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function check(): Health
    {
        try {
            $this->client->describeParameters(['MaxResults' => 1]);

            return Health::up();
        } catch (Throwable $exception) {
            return Health::down(detail: $exception->getMessage());
        }
    }

    /**
     * Returns a copy of the SsmHealthCheck with the discriminator name set.
     *
     * @param string $name The discriminator distinguishing this check in the report.
     * @return SsmHealthCheck A new check carrying the given name.
     */
    public function withName(string $name): SsmHealthCheck
    {
        return new SsmHealthCheck(name: $name, client: $this->client);
    }

    public function component(): string
    {
        return 'ssm';
    }
}
