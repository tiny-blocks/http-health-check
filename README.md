# Http Health Check

[![License](https://img.shields.io/badge/license-MIT-green)](https://github.com/tiny-blocks/http-health-check/blob/main/LICENSE)

* [Overview](#overview)
* [Installation](#installation)
* [How to use](#how-to-use)
    + [Liveness handler](#liveness-handler)
    + [Readiness handler](#readiness-handler)
    + [Database checks with Doctrine](#database-checks-with-doctrine)
    + [AWS SSM checks](#aws-ssm-checks)
    + [Drain marker](#drain-marker)
    + [Custom health checks](#custom-health-checks)
    + [Readiness payload](#readiness-payload)
* [License](#license)
* [Contributing](#contributing)

## Overview

Framework-agnostic, infrastructure-agnostic readiness and liveness checking, exposed over HTTP through
PSR-15 request handlers.

The library separates two concerns. The domain (`HealthCheck`, `Health`, `Status`, `Readiness`,
`HealthChecks`, `HealthReport`) describes dependencies and aggregates their outcome without importing
Doctrine, AWS, or PSR. It is usable outside HTTP. The HTTP adapter (`LivenessHandler`,
`ReadinessHandler`, `DrainMarker`) is the deliverable you route your probes to.

Each dependency is registered as critical or optional. The aggregate readiness follows a single rule:
any critical check down makes the service `UNAVAILABLE`, otherwise any optional check down makes it
`DEGRADED`, otherwise it is `HEALTHY`. An empty set of checks is `HEALTHY` because nothing was
registered. The readiness endpoint maps that outcome to an HTTP status.

| Readiness     | Meaning                                                       | HTTP status |
|---------------|---------------------------------------------------------------|-------------|
| `HEALTHY`     | Every critical check is up and no optional check is down.     | `200`       |
| `DEGRADED`    | Every critical check is up, at least one optional check down. | `200`       |
| `UNAVAILABLE` | At least one critical check is down, or the service drains.   | `503`       |

Two concrete drivers ship with the library: `DoctrineHealthCheck` for databases and `SsmHealthCheck`
for AWS SSM. Their third-party packages are optional. Install `doctrine/dbal` only when you use the
Doctrine driver, and `aws/aws-sdk-php` only when you use the SSM driver. Every check exposes a generic
`component()` category (for example "database"), never a specific host or connection name.

## Installation

```bash
composer require tiny-blocks/http-health-check
```

## How to use

### Liveness handler

The liveness endpoint answers `200` as long as the process can serve the request. It runs no checks
and exists to tell an orchestrator whether the process should be restarted.

```php
<?php

declare(strict_types=1);

use TinyBlocks\HttpHealthCheck\LivenessHandler;

# LivenessHandler is a PSR-15 RequestHandlerInterface.
$handler = LivenessHandler::create();

# Route your liveness endpoint (for example, GET /health/live) to this handler.
# handle() returns a PSR-7 response with status 200 and body {"status":"UP"}.
$response = $handler->handle($request);
```

### Readiness handler

The readiness endpoint runs every registered check and maps the aggregate readiness to an HTTP status.
Register each dependency as critical or optional. A critical failure returns `503`, an optional
failure keeps `200` while reporting `DEGRADED`.

```php
<?php

declare(strict_types=1);

use Aws\Ssm\SsmClient;
use Doctrine\DBAL\DriverManager;
use TinyBlocks\HttpHealthCheck\DoctrineHealthCheck;
use TinyBlocks\HttpHealthCheck\HealthChecks;
use TinyBlocks\HttpHealthCheck\ReadinessHandler;
use TinyBlocks\HttpHealthCheck\SsmHealthCheck;

$connection = DriverManager::getConnection(['url' => 'pdo-pgsql://user:pass@localhost:5432/app']);
$client = new SsmClient(['region' => 'us-east-1', 'version' => 'latest']);

# A failing critical check makes the service UNAVAILABLE (503).
# A failing optional check only marks the service DEGRADED (still 200).
$checks = HealthChecks::createFromEmpty()
    ->withCritical(check: DoctrineHealthCheck::from(connection: $connection)->withName(name: 'primary'))
    ->withOptional(check: SsmHealthCheck::from(client: $client));

# Route your readiness endpoint (for example, GET /health/ready) to this handler.
$handler = ReadinessHandler::from(checks: $checks);

$response = $handler->handle($request);
```

`HealthChecks` is immutable. Every `withCritical` and `withOptional` returns a new set, so the
original is never mutated.

### Database checks with Doctrine

`DoctrineHealthCheck` verifies database availability by issuing a SQL query through a Doctrine DBAL
connection. The default probe is the platform's own dummy select, resolved from the connection, so it
works on any DBAL platform without assuming a specific database. Customize the probe query and the
report name when a service runs more than one database.

```php
<?php

declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use TinyBlocks\HttpHealthCheck\DoctrineHealthCheck;
use TinyBlocks\HttpHealthCheck\HealthChecks;

# doctrine/dbal is only required when you use DoctrineHealthCheck.
$connection = DriverManager::getConnection(['url' => 'pdo-pgsql://user:pass@localhost:5432/app']);

$checks = HealthChecks::createFromEmpty()
    ->withCritical(
        check: DoctrineHealthCheck::from(connection: $connection)
            ->withName(name: 'primary')
            ->withQuery(query: 'SELECT version()')
    );
```

### AWS SSM checks

`SsmHealthCheck` verifies AWS SSM availability through a lightweight describe call. Register it as
optional when the service can still serve traffic while SSM is unreachable.

```php
<?php

declare(strict_types=1);

use Aws\Ssm\SsmClient;
use TinyBlocks\HttpHealthCheck\HealthChecks;
use TinyBlocks\HttpHealthCheck\SsmHealthCheck;

# aws/aws-sdk-php is only required when you use SsmHealthCheck.
$client = new SsmClient(['region' => 'us-east-1', 'version' => 'latest']);

$checks = HealthChecks::createFromEmpty()
    ->withOptional(check: SsmHealthCheck::from(client: $client)->withName(name: 'parameters'));
```

### Drain marker

A drain marker lets an orchestrator gracefully remove an instance from rotation. While the watched
file exists, the readiness endpoint short-circuits to `503` without running any check, so traffic
stops before the process shuts down. Use `default()` to watch `/tmp/draining`, or `from()` to watch a
custom path.

```php
<?php

declare(strict_types=1);

use TinyBlocks\HttpHealthCheck\DrainMarker;
use TinyBlocks\HttpHealthCheck\HealthChecks;
use TinyBlocks\HttpHealthCheck\ReadinessHandler;

# Watch the default path (/tmp/draining).
$default = DrainMarker::default();

# Or watch a custom path.
$custom = DrainMarker::from(path: '/var/run/app/draining');

$handler = ReadinessHandler::from(checks: HealthChecks::createFromEmpty(), drainMarker: $custom);
```

### Custom health checks

Implement `HealthCheck` to verify any dependency. The `component()` is the generic category exposed in
the report, the optional `name()` distinguishes checks that share a component, and `check()` catches
its own infrastructure failures and reports a DOWN `Health` instead of letting the exception escape.

```php
<?php

declare(strict_types=1);

use Throwable;
use TinyBlocks\HttpHealthCheck\Health;
use TinyBlocks\HttpHealthCheck\HealthCheck;
use TinyBlocks\HttpHealthCheck\HealthChecks;

final readonly class RedisHealthCheck implements HealthCheck
{
    public function __construct(private Redis $client)
    {
    }

    public function name(): ?string
    {
        return null;
    }

    public function check(): Health
    {
        # Catch the failure and report DOWN rather than letting it escape.
        try {
            $this->client->ping();

            return Health::up();
        } catch (Throwable $exception) {
            return Health::down(detail: $exception->getMessage());
        }
    }

    public function component(): string
    {
        return 'cache';
    }
}

$checks = HealthChecks::createFromEmpty()->withOptional(check: new RedisHealthCheck(client: $redis));
```

### Readiness payload

A successful readiness response carries the aggregate `status` and one entry per check. The `name` and
`detail` fields appear only when present, so a check without a discriminator omits `name`, and a check
that is up omits `detail`.

```json
{
    "checks": [
        {
            "name": "primary",
            "status": "UP",
            "critical": true,
            "component": "database",
            "duration_in_milliseconds": 4.21
        },
        {
            "detail": "ssm is unavailable",
            "status": "DOWN",
            "critical": false,
            "component": "ssm",
            "duration_in_milliseconds": 12.5
        }
    ],
    "status": "DEGRADED"
}
```

When the service is draining, the readiness endpoint returns `503` with a minimal body and runs no
check.

```json
{
    "status": "UNAVAILABLE"
}
```

## License

Http Health Check is licensed under [MIT](LICENSE).

## Contributing

Please follow the [contributing guidelines](https://github.com/tiny-blocks/tiny-blocks/blob/main/CONTRIBUTING.md) to
contribute to the project.
