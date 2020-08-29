<?php

namespace Rubix\ML\Tests\Persisters;

use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use Rubix\ML\Persistable;
use Rubix\ML\Persisters\Flysystem;
use Rubix\ML\Persisters\Persister;
use Rubix\ML\Classifiers\DummyClassifier;
use PHPUnit\Framework\TestCase;

/**
 * @group Persisters
 * @covers \Rubix\ML\Persisters\Filesystem
 */
class FlysystemTest extends TestCase
{
    /**
     * @var \Rubix\ML\Persistable
     */
    protected $persistable;

    /**
     * @var \Rubix\ML\Persisters\Flysystem
     */
    protected $persister;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @before
     */
    protected function setUp() : void
    {
        $this->path = '/path/to/test.model';
        $this->persistable = new DummyClassifier();
        $this->filesystem = new Filesystem(new MemoryAdapter());
        $this->persister = new Flysystem($this->path, $this->filesystem);
    }

    /**
     * @after
     */
    protected function tearDown() : void
    {
        if ($this->filesystem->has($this->path)) {
            $this->filesystem->delete($this->path);
        }
    }

    /**
     * @test
     */
    public function build() : void
    {
        $this->assertInstanceOf(Flysystem::class, $this->persister);
        $this->assertInstanceOf(Persister::class, $this->persister);
    }

    /**
     * @test
     */
    public function saveLoad() : void
    {
        $this->persister->save($this->persistable);
        $this->assertTrue(
            $this->filesystem->has($this->path),
            'Persistable was not saved as expected'
        );

        $model = $this->persister->load();
        $this->assertInstanceOf(get_class($this->persistable), $model);
        $this->assertInstanceOf(Persistable::class, $model);
    }

    protected function assertPreConditions() : void
    {
        $this->assertFalse(
            $this->filesystem->has($this->path),
            sprintf('File already exists in filesystem at path "%s"', $this->path)
        );
    }
}
