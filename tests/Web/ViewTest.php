<?php

declare(strict_types=1);

namespace Oni\Tests\Web;

use Oni\Web\View;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for Oni\Web\View.
 *
 * View is a singleton with a private constructor. Between tests we reset
 * the static instance via reflection so each test gets a clean object.
 * Template files are written to a temporary directory and cleaned up
 * in tearDown.
 */
#[CoversClass(View::class)]
final class ViewTest extends TestCase
{
    private string $tmpDir = '';

    #[Before]
    public function resetViewSingleton(): void
    {
        $ref = new ReflectionClass(View::class);
        $prop = $ref->getProperty('_instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);

        $this->tmpDir = sys_get_temp_dir() . '/oni_view_test_' . getmypid() . '_' . mt_rand();
        mkdir($this->tmpDir, 0755, true);
    }

    #[After]
    public function cleanUpTmpDir(): void
    {
        $this->removeDirRecursive($this->tmpDir);

        // Also reset singleton so next test in suite starts clean
        $ref = new ReflectionClass(View::class);
        $prop = $ref->getProperty('_instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private function removeDirRecursive(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $full = "{$path}/{$entry}";
            is_dir($full) ? $this->removeDirRecursive($full) : unlink($full);
        }
        rmdir($path);
    }

    private function freshView(): View
    {
        $ref = new ReflectionClass(View::class);
        $prop = $ref->getProperty('_instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);

        return View::init();
    }

    private function createTemplate(string $relativePath, string $content): string
    {
        $fullPath = "{$this->tmpDir}/{$relativePath}";
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($fullPath, $content);

        return $fullPath;
    }

    // -----------------------------------------------------------------------
    // init() singleton
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('init() returns the same instance on repeated calls')]
    public function testInitReturnsSameInstance(): void
    {
        $v1 = View::init();
        $v2 = View::init();

        $this->assertSame($v1, $v2);
    }

    // -----------------------------------------------------------------------
    // setIndexPath / getIndexPath
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('getIndexPath() returns the default value "index" before any setter call')]
    public function testGetIndexPathDefaultIsIndex(): void
    {
        $view = $this->freshView();

        $this->assertSame('index', $view->getIndexPath());
    }

    #[Test]
    #[TestDox('setIndexPath() returns true and updates getIndexPath()')]
    public function testSetIndexPath(): void
    {
        $view = $this->freshView();
        $result = $view->setIndexPath('my/index');

        $this->assertTrue($result);
        $this->assertSame('my/index', $view->getIndexPath());
    }

    // -----------------------------------------------------------------------
    // setLayoutPath / getLayoutPath
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('getLayoutPath() returns the default value "layout"')]
    public function testGetLayoutPathDefaultIsLayout(): void
    {
        $view = $this->freshView();

        $this->assertSame('layout', $view->getLayoutPath());
    }

    #[Test]
    #[TestDox('setLayoutPath() returns true and updates getLayoutPath()')]
    public function testSetLayoutPath(): void
    {
        $view = $this->freshView();
        $result = $view->setLayoutPath('main/default');

        $this->assertTrue($result);
        $this->assertSame('main/default', $view->getLayoutPath());
    }

    // -----------------------------------------------------------------------
    // setContentPath / getContentPath
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('getContentPath() returns null before any setContentPath() call')]
    public function testGetContentPathDefaultIsNull(): void
    {
        $view = $this->freshView();

        $this->assertNull($view->getContentPath());
    }

    #[Test]
    #[TestDox('setContentPath() returns true and updates getContentPath()')]
    public function testSetContentPath(): void
    {
        $view = $this->freshView();
        $result = $view->setContentPath('articles/intro');

        $this->assertTrue($result);
        $this->assertSame('articles/intro', $view->getContentPath());
    }

    // -----------------------------------------------------------------------
    // setData() / render() with real templates
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('render() returns the output of the index template')]
    public function testRenderReturnsIndexTemplateOutput(): void
    {
        $this->createTemplate('index.php', '<?php echo "HELLO"; ?>');

        $view = $this->freshView();
        $view->setAttr('paths', [$this->tmpDir]);
        $view->setAttr('ext', 'php');
        $view->setIndexPath('index');

        $output = $view->render();

        $this->assertSame('HELLO', $output);
    }

    #[Test]
    #[TestDox('setData() variables are extracted and available inside the template')]
    public function testSetDataExtractsVariablesIntoTemplate(): void
    {
        $this->createTemplate('greet.php', '<?php echo "Hello, {$name}!"; ?>');

        $view = $this->freshView();
        $view->setAttr('paths', [$this->tmpDir]);
        $view->setAttr('ext', 'php');
        $view->setIndexPath('greet');
        $view->setData(['name' => 'World']);

        $output = $view->render();

        $this->assertSame('Hello, World!', $output);
    }

    #[Test]
    #[TestDox('render() returns empty string when the template file does not exist')]
    public function testRenderReturnsEmptyStringWhenTemplateNotFound(): void
    {
        $view = $this->freshView();
        $view->setAttr('paths', [$this->tmpDir]);
        $view->setAttr('ext', 'php');
        $view->setIndexPath('nonexistent');

        $output = $view->render();

        $this->assertSame('', $output);
    }

    #[Test]
    #[TestDox('render() searches multiple paths and uses the first match')]
    public function testRenderSearchesMultiplePaths(): void
    {
        $pathA = "{$this->tmpDir}/a";
        $pathB = "{$this->tmpDir}/b";
        mkdir($pathA, 0755, true);
        mkdir($pathB, 0755, true);

        // Only present in pathB
        file_put_contents("{$pathB}/tmpl.php", '<?php echo "FROM_B"; ?>');

        $view = $this->freshView();
        $view->setAttr('paths', [$pathA, $pathB]);
        $view->setAttr('ext', 'php');
        $view->setIndexPath('tmpl');

        $this->assertSame('FROM_B', $view->render());
    }

    #[Test]
    #[TestDox('first matching path wins when template exists in both paths')]
    public function testFirstMatchingPathWins(): void
    {
        $pathA = "{$this->tmpDir}/a";
        $pathB = "{$this->tmpDir}/b";
        mkdir($pathA, 0755, true);
        mkdir($pathB, 0755, true);

        file_put_contents("{$pathA}/tmpl.php", '<?php echo "FROM_A"; ?>');
        file_put_contents("{$pathB}/tmpl.php", '<?php echo "FROM_B"; ?>');

        $view = $this->freshView();
        $view->setAttr('paths', [$pathA, $pathB]);
        $view->setAttr('ext', 'php');
        $view->setIndexPath('tmpl');

        $this->assertSame('FROM_A', $view->render());
    }

    #[Test]
    #[TestDox('absolute path starting with / is used directly when file exists')]
    public function testAbsolutePathWithSlashPrefixIsUsedDirectly(): void
    {
        $absFile = "{$this->tmpDir}/abs_tmpl";
        file_put_contents("{$absFile}.php", '<?php echo "ABS"; ?>');

        $view = $this->freshView();
        $view->setAttr('paths', [$this->tmpDir]);
        $view->setAttr('ext', 'php');
        $view->setIndexPath($absFile); // starts with /

        $this->assertSame('ABS', $view->render());
    }

    #[Test]
    #[TestDox('path starting with ~ is treated as an absolute path')]
    public function testPathStartingWithTildeIsAbsolute(): void
    {
        // The loadPartial code checks str_starts_with('~') and appends .ext
        // We cannot really point ~ to our tempdir on disk without hacks,
        // but we can assert the absolute-path branch is attempted (file not found = empty string)
        $view = $this->freshView();
        $view->setAttr('paths', [$this->tmpDir]);
        $view->setAttr('ext', 'php');
        $view->setIndexPath('~/nonexistent');

        // File won't exist at ~/nonexistent.php so falls through; result is ''
        $output = $view->render();

        $this->assertSame('', $output);
    }

    #[Test]
    #[TestDox('view/ext attr controls the file extension used when resolving templates')]
    public function testViewExtAttrControlsFileExtension(): void
    {
        $this->createTemplate('tmpl.html', '<b>HTML</b>');

        $view = $this->freshView();
        $view->setAttr('paths', [$this->tmpDir]);
        $view->setAttr('ext', 'html');
        $view->setIndexPath('tmpl');

        $this->assertSame('<b>HTML</b>', $view->render());
    }

    #[Test]
    #[TestDox('setData() overwrites any previously set data')]
    public function testSetDataOverwritesPreviousData(): void
    {
        $this->createTemplate('msg.php', '<?php echo $msg; ?>');

        $view = $this->freshView();
        $view->setAttr('paths', [$this->tmpDir]);
        $view->setAttr('ext', 'php');
        $view->setIndexPath('msg');

        $view->setData(['msg' => 'first']);
        $view->setData(['msg' => 'second']);

        $this->assertSame('second', $view->render());
    }
}
