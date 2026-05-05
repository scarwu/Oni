# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Oni is a lightweight PHP framework for Web & CLI applications, published as `scarwu/oni` on Packagist. The library code lives in `src/Oni`; `example/` contains reference Web and CLI apps that import the framework via Composer's vendor autoloader.

`composer.json` requires `php: >=8.4` and `phpunit/phpunit: ^13`. Source uses `mixed` return types and matched argument nullability throughout.

## Commands

- `./setup.sh` — runs `composer install` (just installs vendor/)
- Serve the Web example with the built-in PHP server: `php -S localhost:8000 -t example/Web/boot` (the boot directory has an `.htaccess` for Apache)
- `./example/CLI/boot.php <task> [args] [-options] [--configs]` — runs the CLI example; entry is shebanged `#!/usr/bin/env php`
- `./vendor/bin/phpunit` — runs the test suite (`phpunit.xml` at the repo root; `failOnWarning` and `failOnRisky` are on, so warnings and risky tests fail the run). Single file: `./vendor/bin/phpunit tests/Core/LoaderTest.php`. Single test: `./vendor/bin/phpunit --filter testMethodName tests/Core/LoaderTest.php`. Test classes live under the `Oni\Tests\` PSR-4 namespace (`autoload-dev` in composer.json), with fixtures under `tests/fixtures/` (sample tasks, views, static files) referenced by tests via absolute paths.

Commit messages in history follow the form `1. <verb> <subject>`; match that style if committing.

## Architecture

Two parallel stacks (`Oni\Web` and `Oni\CLI`) share a tiny `Oni\Core` foundation.

**`Oni\Core\Loader`** is a singleton PSR-0-ish autoloader that accepts multiple `(namespace → path)` registrations. `Web\App::up()` and `CLI\App::up()` call `Loader::append(...)` to register the *consumer app's* controller/task namespace before dispatch — the framework itself is loaded via Composer's normal autoload (`"psr-0": {"Oni": "src"}`).

**`Oni\Core\Basic`** is an abstract base providing a `_attr` key/value bag with `setAttr`/`getAttr`. Most classes (App, controllers, tasks, View) extend it and use `_attr` for configuration with slash-separated keys like `'controller/namespace'`, `'router/action/default'`, `'cache/time'`.

### Web dispatch (`Oni\Web\App::run`)

Order of attempts for each request:
1. `loadStatic()` — GET only; serve files under any `static/paths` directory, with a small extension→MIME mapping (`.php` is explicitly rejected).
2. `loadCache()` — GET only; serve `{cache/path}/md5(uri)` if younger than `cache/time` (default 300s), else delete and continue. `saveCache()` runs after successful page renders.
3. `loadController()` — the router.

The router splits the URI on `/`, then walks the segments consuming them as long as they match a subdirectory or `{Segment}Controller.php` file under `controller/path`. Remaining segments become `$params`. So `/about/mvc/extra` resolves to `About\MvcController` with `$params = ['extra']`. If nothing matches, it falls back to `router/controller/default` (default `main`).

Three controller modes, selected via `_attr['mode']` on the controller base class:

- **`page`** (`Oni\Web\Controller\Page`) — next param is the action name (default `router/action/default` = `'default'`). Calls `{action}Action($params)`, then `View::render()`, saves to cache on GET, emits HTML. If action missing, falls back to the default controller's `router/action/error` action (default `'error'`), returning 404 if that also fails.
- **`ajax`** (`Oni\Web\Controller\Ajax`) — like page but the action's return value is JSON-encoded via `Res::json()`. Missing action → 501.
- **`rest`** (`Oni\Web\Controller\Rest`) — action name is the HTTP method (`getAction`, `postAction`, ...). Missing method handler → 501.

Controller lifecycle around the action call: `up()` → `{action}Action($params)` → `down()`. If either `App::up()` or `Controller::up()` returns `false`, dispatch halts.

### View (`Oni\Web\View`, singleton)

Three-level template chain with defaults: `indexPath='index'` → `layoutPath=<controller>/<action>` (set by the App, lowercased) → `contentPath=null` (opt-in via `$this->view->setContentPath(...)` from a controller). Templates are plain PHP files under any `view/paths` directory with configurable extension (`view/ext`, default `php`). Inside a template, `$this->loadLayout()` / `$this->loadContent()` pull in the next level. Data passed via `View::setData([...])` is extracted to local variables inside templates. Paths starting with `~` or `/` are treated as absolute.

### CLI dispatch (`Oni\CLI\App::run`)

Same path-traversal router as Web, but over `IO::getArguments()` (positional argv entries) and resolving to `{Name}Task.php`. Fallback task is `router/task/default` (default `'main'`). The remaining arguments are passed to `Task::run($params)`. Task lifecycle: `up()` → `run($params)` → `down()`.

**`Oni\CLI\IO`** (singleton) parses `$_SERVER['argv']` into three buckets at construction time:
- **arguments** — positional (`help`, `read`)
- **options** — `-x` or `-x value` (short flags, single dash)
- **configs** — `--key` or `--key=value` (long flags, double dash)

Note the split: options and configs are distinct buckets, not aliases. `getOptions()`/`getConfigs()`/`getArguments()` retrieve them. `IO` also provides colored output (`info`/`notice`/`warning`/`error`), `ask()`, and an interactive `menuSelector()` using ANSI escape codes from `Oni\CLI\Helper\ANSIEscapeCode`.

### Consumer app layout (see `example/`)

Web apps provide a boot script that configures the App:
- `controller/namespace` + `controller/path` (the app registers these with `Loader` itself)
- `view/paths` (array), `static/paths` (array), `cache/path`

CLI apps only need `task/namespace` + `task/path`. Both Apps support `router/event/up` and `router/event/down` callables as global hooks run inside the App's own lifecycle.
