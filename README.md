# Oni

A lightweight PHP framework for Web and CLI applications.

[![Build Status](https://travis-ci.org/scarwu/Oni.png?branch=master)](https://travis-ci.org/scarwu/Oni)

## Requirements

- PHP 8.4+
- Composer

## Installation

```sh
composer require scarwu/oni
```

For local development:

```sh
./setup.sh
```

## Project Layout

```text
src/Oni/Core     Shared base class and namespace loader
src/Oni/Web      Web app, controllers, request/response, views, stores
src/Oni/CLI      CLI app, task base class, IO, ANSI helpers
example/Web      Example web application
example/CLI      Example CLI application
tests            PHPUnit test suite and fixtures
docs/specs       Current behavior specifications
```

## Core Concepts

Oni has two application stacks that share `Oni\Core`:

- `Oni\Web\App` handles HTTP requests, static files, cached pages, controller dispatch, and view rendering.
- `Oni\CLI\App` handles command-line task routing and task lifecycle execution.
- `Oni\Core\Loader` registers application namespaces to filesystem paths.
- `Oni\Core\Basic` provides the shared `setAttr()` / `getAttr()` configuration container.

## Web Applications

A web app configures controller, view, static, and cache paths before calling `run()`.

```php
<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

$root = __DIR__ . '/..';

$app = new Oni\Web\App();
$app->setAttr('controller/namespace', 'WebApp\Controller');
$app->setAttr('controller/path', "{$root}/controllers");
$app->setAttr('view/paths', ["{$root}/views"]);
$app->setAttr('static/paths', ["{$root}/static"]);
$app->setAttr('cache/path', "{$root}/caches");
$app->run();
```

Controllers are named `{Name}Controller` and actions are named `{action}Action`.

Supported controller modes:

- `Page`: renders a view and can write GET responses to cache.
- `Ajax`: returns action data as JSON.
- `Rest`: maps HTTP methods to actions such as `getAction()` and `postAction()`.

Run the web example:

```sh
php -S localhost:8000 -t example/Web/boot
```

## CLI Applications

A CLI app configures the task namespace/path and an optional default task before calling `run()`.

```php
#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

$root = __DIR__;

$app = new Oni\CLI\App();
$app->setAttr('task/namespace', 'CLIApp\Task');
$app->setAttr('task/path', "{$root}/tasks");
$app->setAttr('router/task/default', 'Help');
$app->run();
```

Tasks are named `{Name}Task` and implement `run(array $params = []): void`.

Task lifecycle:

```text
up() -> run($params) -> down()
```

`Oni\CLI\IO` parses command input into:

- `arguments`: positional values
- `options`: short options such as `-x` or `-x value`
- `configs`: long configs such as `--key` or `--key=value`

Run the CLI example:

```sh
php example/CLI/boot.php Help
```

## Testing

Run the test suite:

```sh
vendor/bin/phpunit
```

The test suite covers core attributes, autoloading, CLI argument parsing, CLI task lifecycle, request accessors, view rendering, and HTML helper output.

## Specifications

Current behavior is documented in:

- `docs/specs/core.md`
- `docs/specs/cli.md`
- `docs/specs/web.md`

These files describe the expected behavior that the automated tests should preserve.

## License

Oni is released under the MIT License.
