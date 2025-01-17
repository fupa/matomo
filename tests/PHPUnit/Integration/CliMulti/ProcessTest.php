<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\CliMulti;

use Piwik\CliMulti\Process;
use Piwik\Tests\Framework\Mock\File;
use ReflectionProperty;

/**
 * @group CliMulti
 */
class ProcessTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Process
     */
    private $process;

    public function setUp(): void
    {
        parent::setUp();

        File::reset();
        $this->process = new Process('testPid');
    }

    public function tearDown(): void
    {
        if (is_object($this->process)) {
            $this->process->finishProcess();
        }
        File::reset();
    }

    public function test_construct_shouldFailInCasePidIsInvalid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The given pid has an invalid format');

        new Process('../../htaccess');
    }

    public function test_getPid()
    {
        $this->assertSame('testPid', $this->process->getPid());
    }

    public function test_construct_shouldBeNotStarted_IfPidJustCreated()
    {
        $this->assertFalse($this->process->hasStarted());
    }

    public function test_construct_shouldBeNotRunning_IfPidJustCreated()
    {
        if (! Process::isSupported()) {
            $this->markTestSkipped('Not supported');
        }

        $this->assertFalse($this->process->isRunning());
    }

    public function test_startProcess_finishProcess_ShouldMarkProcessAsStarted()
    {
        if (! Process::isSupported()) {
            $this->markTestSkipped('Not supported');
        }

        $this->assertFalse($this->process->isRunning());
        $this->assertFalse($this->process->hasStarted());
        $this->assertFalse($this->process->hasFinished());

        $this->process->startProcess();

        $this->assertTrue($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertTrue($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertFalse($this->process->hasFinished());

        $this->process->startProcess();

        $this->assertTrue($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertFalse($this->process->hasFinished());

        $this->process->finishProcess();

        $this->assertFalse($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertTrue($this->process->hasFinished());
    }

    public function test_isRunning_ShouldMarkProcessAsFinished_IfPidFileIsTooBig()
    {
        if (! Process::isSupported()) {
            $this->markTestSkipped('Not supported');
        }

        $this->process->startProcess();
        $this->assertTrue($this->process->isRunning());
        $this->assertFalse($this->process->hasFinished());

        $this->process->writePidFileContent(str_pad('1', 505, '1'));

        $this->assertFalse($this->process->isRunning());
        $this->assertTrue($this->process->hasFinished());
    }

    public function test_finishProcess_ShouldNotThrowError_IfNotStartedBefore()
    {
        $this->process->finishProcess();

        $this->assertFalse($this->process->isRunning());
        $this->assertTrue($this->process->hasStarted());
        $this->assertTrue($this->process->hasFinished());
    }

    public function test_hasStarted_startedWhenContentFalse()
    {
        $this->assertTrue($this->process->hasStarted(false));
    }

    public function test_hasStarted_startedWhenPidGiven()
    {
        $this->assertTrue($this->process->hasStarted('6341'));
        // remembers the process was started at some point
        $this->assertTrue($this->process->hasStarted(''));
    }

    public function test_hasStarted_notStartedYetEmptyContentInPid()
    {
        $this->assertFalse($this->process->hasStarted(''));
    }

    public function test_getSecondsSinceCreation()
    {
        // This is not proper, but it avoids using sleep and stopping the tests for several seconds
        $r = new ReflectionProperty($this->process, 'timeCreation');
        $r->setAccessible(true);
        $r->setValue($this->process, time() - 2);

        $seconds = $this->process->getSecondsSinceCreation();

        $this->assertEquals(2, $seconds);
    }
}
