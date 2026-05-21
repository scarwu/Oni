# Repository Guidelines

## Prepare
Before major edits, read `docs/rules/karpathy-guidelines.md` and keep every change directly tied to the task.

## Project Structure & Module Organization
Oni is a lightweight PHP framework for Web and CLI apps (`scarwu/oni`).
- `src/Oni/Core`: shared base (`Basic`) and autoloader (`Loader`).
- `src/Oni/Web`: HTTP stack (`App`, controllers, request/response, `View`, cache/store).
- `src/Oni/CLI`: CLI stack (`App`, `IO`, `Task`).
- `example/Web`, `example/CLI`: reference consumer apps.
- `tests/fixtures`: test fixture scaffold (no committed test cases yet).

## Build, Test, and Development Commands
- `./setup.sh`: install dependencies via Composer.
- `composer install`: same as setup script.
- `php -S localhost:8000 -t example/Web/boot`: run web example locally.
- `php example/CLI/boot.php Help`: run CLI example entry/task.
- `vendor/bin/phpunit tests`: run tests after adding test files under `tests/`.

## Architecture Overview
Two parallel stacks (`Oni\Web`, `Oni\CLI`) share `Oni\Core`.
- `Oni\Core\Loader` registers consumer namespaces to paths (`Loader::append(...)`) before dispatch.
- `Oni\Core\Basic` stores app/controller/task config via `_attr` and `setAttr/getAttr`.
- Web dispatch order in `Oni\Web\App::run`: `loadStatic()` -> `loadCache()` -> `loadController()`.
- Controller modes:
  - `Page`: `{action}Action($params)`, render view, optional cache write.
  - `Ajax`: action return is JSON response.
  - `Rest`: action maps to HTTP method (`getAction`, `postAction`, ...).
- CLI dispatch resolves `{Name}Task.php`, then runs lifecycle `up()` -> `run($params)` -> `down()`.
- `Oni\CLI\IO` parses three buckets: positional `arguments`, short `options` (`-x`), long `configs` (`--key`/`--key=value`).

## Coding Style & Naming Conventions
- Target PHP `>=8.4` (matches current `composer.json`).
- Follow existing style: 4-space indentation, braces on new lines, explicit boolean comparisons (`true === ...`).
- Namespaces/classes: `Oni\...` and `PascalCase`.
- Web class naming: `{Name}Controller`, action methods `{action}Action`.
- CLI class naming: `{Name}Task`, implement `run(array $params)`.

## Testing Guidelines
- Framework: PHPUnit (`phpunit/phpunit`).
- Add tests under `tests/` with `*Test.php` filenames.
- Keep fixtures in `tests/fixtures/<feature>/`.
- Prioritize routing, dispatch lifecycle, response mode, and cache behavior tests.

## Commit & Pull Request Guidelines
- Match existing commit style: `1. <verb> <subject>` (example: `1. refactor loader`).
- Keep commits scoped to one logical change.
- PRs should include summary, linked issues, verification steps, and output/screenshot evidence when behavior changes.
