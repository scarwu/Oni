<?php
declare(strict_types=1);

namespace Tests\Fixtures\Tasks;

use Oni\CLI\Task;

final class SkipTask extends Task
{
    public function up(): bool
    {
        Lifecycle::$events[] = 'skip.up';

        return false;
    }

    #[\Override]
    public function run(array $params = []): void
    {
        Lifecycle::$events[] = 'skip.run';
    }

    public function down(): void
    {
        Lifecycle::$events[] = 'skip.down';
    }
}
