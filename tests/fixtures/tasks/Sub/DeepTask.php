<?php

declare(strict_types=1);

namespace Oni\Tests\Fixture\Task\Sub;

use Oni\CLI\Task;

class DeepTask extends Task
{
    public array $receivedParams = [];

    public function run(array $params = []): void
    {
        $this->receivedParams = $params;
    }
}
