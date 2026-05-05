<?php

declare(strict_types=1);

namespace Oni\Tests\Fixture\Task;

use Oni\CLI\Task;

class MainTask extends Task
{
    public array $receivedParams = [];
    public bool $upCalled = false;
    public bool $downCalled = false;

    public function up(): mixed
    {
        $this->upCalled = true;

        return true;
    }

    public function run(array $params = []): void
    {
        $this->receivedParams = $params;
    }

    public function down(): void
    {
        $this->downCalled = true;
    }
}
