# Oni

A lightweight PHP framework for Web and CLI applications.

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
example/CLI      Example CLI application
tests            PHPUnit test suite and fixtures
docs/specs       Current behavior specifications
```

## Core Concepts

Oni has two application stacks that share `Oni\Core`:

- `Oni\Web\App` handles HTTP requests, static files, cached pages, controller dispatch, and view rendering.
- `Oni\CLI\App` handles command-line task routing and task lifecycle execution.
- `Oni\Core\Loader` registers application namespaces to filesystem paths via `Loader::append($namespace, $path)`.
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

Optional lifecycle hooks run before and after each request:

```php
$app->setAttr('router/event/up',   function() { /* before dispatch */ });
$app->setAttr('router/event/down', function() { /* after dispatch  */ });
```

Controllers are named `{Name}Controller` and actions are named `{action}Action`.

Supported controller modes:

- `Page`: renders a view and can write GET responses to cache.
- `Ajax`: returns action data as JSON.
- `Rest`: maps HTTP methods to actions such as `getAction()` and `postAction()`.

### View

`Oni\Web\View` is a singleton injected by `Web\App` into Page controllers. Key methods:

- `setData(array $data)` — exposes variables to view templates.
- `setIndexPath(string $path)` — overrides the default `index` template path.
- `setLayoutPath(string $path)` — sets the layout template path.
- `setContentPath(string $path)` — sets the content partial path.
- `render()` — renders and returns the output as a string.

### Model

`Oni\Web\Model` is a thin base class that extends `Oni\Core\Basic`, providing the `setAttr()` / `getAttr()` configuration container for application models.

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

Run the CLI example:

```sh
php example/CLI/boot.php Help
```

### IO

`Oni\CLI\IO` is a singleton that parses command input and provides output helpers.

**Input parsing** splits `$argv` into three buckets:

- `arguments`: positional values
- `options`: short flags such as `-x` or `-x value`
- `configs`: long options such as `--key` or `--key=value`

Access methods: `getArguments()`, `getOptions()`, `getConfigs()` and their `has*` counterparts.

**Output helpers:**

```php
$io->write($text, $fgColor, $bgColor);   // write without newline
$io->writeln($text, $fgColor, $bgColor); // write with newline
$io->error($text);    // red
$io->warning($text);  // yellow
$io->notice($text);   // green
$io->info($text);     // bright black (dim)
$io->debug($text);    // white
$io->log($text);      // plain
```

**Interactive methods:**

- `ask(string $text, ?callable $callback)` — prompts the user, repeating until the callback returns `true`.
- `menuSelector(string $text, array $options)` — interactive arrow-key menu; returns the selected index (0-based) or `null` for an empty list.

### ANSIEscapeCode

`Oni\CLI\Helper\ANSIEscapeCode` provides ANSI escape code constants and helpers for cursor movement, color output (`color()`), and cursor visibility (`cursorShow()` / `cursorHide()`).

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
