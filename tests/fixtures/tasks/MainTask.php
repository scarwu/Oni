<?php
declare(strict_types=1);

namespace Tests\Fixtures\Tasks;

use Oni\CLI\Task;

final class MainTask extends Task
{
    public function up(): bool
    {
        Lifecycle::$events[] = 'main.up';

        return true;
    }

    #[\Override]
    public function run(array $params = []): void
    {
        Lifecycle::$events[] = 'main.run:' . json_encode($params);
    }

    public function down(): void
    {
        Lifecycle::$events[] = 'main.down';
    }
}
