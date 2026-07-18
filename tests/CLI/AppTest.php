<?php
declare(strict_types=1);

namespace Oni\Tests\CLI;

use Oni\CLI\App;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Tasks\Lifecycle;

final class AppTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testRunsDefaultTaskLifecycle(): void
    {
        $_SERVER['argv'] = ['boot.php'];

        $app = new App();
        $app->setAttr('task/namespace', 'Tests\\Fixtures\\Tasks');
        $app->setAttr('task/path', dirname(__DIR__) . '/fixtures/tasks');

        self::assertTrue($app->run());
        self::assertSame(['main.up', 'main.run:[]', 'main.down'], Lifecycle::$events);
    }

    #[RunInSeparateProcess]
    public function testTaskDownRunsWhenTaskUpReturnsFalse(): void
    {
        $_SERVER['argv'] = ['boot.php'];

        $app = new App();
        $app->setAttr('router/task/default', 'skip');
        $app->setAttr('task/namespace', 'Tests\\Fixtures\\Tasks');
        $app->setAttr('task/path', dirname(__DIR__) . '/fixtures/tasks');

        self::assertTrue($app->run());
        self::assertSame(['skip.up', 'skip.down'], Lifecycle::$events);
    }
}
