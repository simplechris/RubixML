<?php

namespace Rubix\ML\Persisters;

use League\Flysystem\FilesystemInterface;
use Rubix\ML\Other\Helpers\Params;
use Rubix\ML\Persistable;
use Rubix\ML\Persisters\Serializers\Native;
use Rubix\ML\Persisters\Serializers\Serializer;
use Stringable;

/**
 * Flysystem
 *
 * Flysystem provides a common API to many filesystems/storage backends.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class Flysystem implements Persister, Stringable
{
    /**
     * The serializer used to convert to and from serial format.
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $path;

    /**
     * Flysystem constructor.
     *
     * @param string $path The filename of the model on the filesystem.
     * @param FilesystemInterface $filesystem The filesystem used to persist the model identified by filename
     * @param Serializer|null $serializer The serializer used whilst persisting the model
     */
    public function __construct(string $path, FilesystemInterface $filesystem, ?Serializer $serializer = null)
    {
        $this->path = $path;
        $this->filesystem = $filesystem;
        $this->serializer = $serializer ?? new Native();
    }

    public function save(Persistable $persistable) : void
    {
        $this->filesystem->write(
            $this->path,
            $this->serializer->serialize($persistable)
        );
    }

    public function load() : Persistable
    {
        return $this->serializer->unserialize(
            $this->filesystem->read($this->path)
        );
    }

    public function __toString() : string
    {
        $fs = 'Flysystem';
        if (method_exists($this->filesystem, 'getAdapter')) {
            $fs .= " (".Params::shortName($this->filesystem->getAdapter()).")";
        }

        return sprintf(
            'Filename: %s, Filesystem: %s, Serializer: %s',
            Params::toString($this->path),
            Params::toString($fs),
            Params::toString($this->serializer)
        );
    }
}
