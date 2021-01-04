<?php

namespace Rubix\ML\Tests\Persisters;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Memory\MemoryAdapter;
use Rubix\ML\Persistable;
use Rubix\ML\Persisters\Flysystem1;
use Rubix\ML\Persisters\Persister;
use Rubix\ML\Classifiers\DummyClassifier;
use RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @group Persisters
 * @covers \Rubix\ML\Persisters\Flysystem1
 */
class Flysystem1Test extends TestCase
{
    /**
     * @var string
     */
    const PATH = '/path/to/test.model';

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Rubix\ML\Persistable
     */
    protected $persistable;

    /**
     * @var \Rubix\ML\Persisters\Flysystem1
     */
    protected $persister;

    /**
     * @before
     */
    protected function setUp() : void
    {
        if (!interface_exists('League\Flysystem\FilesystemInterface')) {
            $this->markTestSkipped('Flysystem2 is unavailable. Skipping tests...');
        }

        if (interface_exists(FilesystemInterface::class)) {
            $this->filesystem = new Filesystem(new MemoryAdapter());
            $this->persistable = new DummyClassifier();
            $this->persister = new Flysystem1(self::PATH, $this->filesystem);
        }
    }

    /**
     * @after
     */
    protected function tearDown() : void
    {
        if ($this->filesystem instanceof FilesystemInterface && $this->filesystem->has(self::PATH)) {
            $this->filesystem->delete(self::PATH);
        }
    }

    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Flysystem1::class, $this->persister);
        $this->assertInstanceOf(Persister::class, $this->persister);
    }

    /**
     * @test
     */
    public function saveLoad() : void
    {
        $this->persister->save($this->persistable);
        $this->assertTrue($this->filesystem->has(self::PATH));

        $model = $this->persister->load();

        $this->assertInstanceOf(DummyClassifier::class, $model);
        $this->assertInstanceOf(Persistable::class, $model);
    }

    /**
     * @test
     */
    public function testSaveMethodWhenFilesystemWriteFails() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('(^Could not write to filesystem)');

        $filesystem = $this->createMock(FilesystemInterface::class);
        $filesystem->method('put')->with(self::PATH)->willReturn(false); // false: WRITE FAILED

        $this->persister = new Flysystem1(self::PATH, $filesystem);
        $this->persister->save($this->persistable);
    }

    /**
     * @test
     */
    public function testSaveMethodWithHistoryDisabled() : void
    {
        $directory = dirname(self::PATH);
        $this->persister = new Flysystem1(self::PATH, $this->filesystem, false);

        // Save
        $this->persister->save($this->persistable);
        $this->assertCount(1, $this->filesystem->listContents($directory));
        $this->assertTrue($this->filesystem->has(self::PATH));

        // Save again. As history is disabled the existing model will be overwritten:
        $this->persister->save($this->persistable);
        $this->assertCount(1, $this->filesystem->listContents($directory));
        $this->assertTrue($this->filesystem->has(self::PATH));
    }

    /**
     * @test
     */
    public function testSaveMethodWithHistoryEnabled() : void
    {
        $directory = dirname(self::PATH);
        $this->persister = new Flysystem1(self::PATH, $this->filesystem, true);

        // Save
        $this->persister->save($this->persistable);
        $this->assertTrue($this->filesystem->has(self::PATH));

        // Save again to the same path. The existing model will be renamed before saving the new model:
        $this->persister->save($this->persistable);
        $files = $this->filesystem->listContents($directory);
        $this->assertCount(2, $files);
        foreach ($files as $file) {
            $this->assertStringContainsString(self::PATH, '/' . $file['path']);
        }
    }

    /**
     * @test
     */
    public function testSaveMethodWhenHistoryCreationFails() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('(^Failed to create history file:)');

        $mock = $this->createMock(FilesystemInterface::class);
        $mock->expects($this->any())
            ->method('has')
            ->willReturn($this->onConsecutiveCalls(true, true, false));
        $mock->expects($this->any())
            ->method('rename')
            ->willReturn(false);

        $this->persister = new Flysystem1(self::PATH, $mock, true);
        $this->persister->save($this->persistable);
    }

    /**
     * @test
     */
    public function testSaveMethodWhenHistoryCreationInternallyFails() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('(^Failed to create history file:)');

        $mock = $this->createMock(FilesystemInterface::class);
        $mock->expects($this->any())
            ->method('has')
            ->willReturn($this->onConsecutiveCalls(true, true, false));
        $mock->expects($this->any())
            ->method('rename')
            ->willThrowException(new FileNotFoundException(self::PATH));

        $this->persister = new Flysystem1(self::PATH, $mock, true);
        $this->persister->save($this->persistable);
    }

    /**
     * @test
     */
    public function testLoadMethodWhenTargetNotExists() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('(^File does not exist in filesystem)');

        $this->persister->load();
    }

    /**
     * @test
     */
    public function testLoadMethodWhenTargetIsEmpty() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('(^File does not contain any data)');

        $this->filesystem->put(self::PATH, '');
        $this->persister->load();
    }

    protected function assertPreConditions() : void
    {
        $this->assertFalse($this->filesystem->has(self::PATH));
    }
}