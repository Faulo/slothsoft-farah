<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Streams;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use BadMethodCallException;

class GeneratorStream implements StreamInterface
{

    private $writer;

    private $generator;

    public function __construct(ChunkWriterInterface $writer)
    {
        $this->writer = $writer;
        $this->generator = $writer->toChunks();
    }

    public function eof()
    {
        return ! $this->generator->valid();
    }

    public function rewind()
    {
        $this->generator = $this->writer->toChunks();
    }

    public function close()
    {
        $this->writer = null;
        $this->generator = null;
    }

    public function detach()
    {
        $this->writer = null;
        $this->generator = null;
    }

    public function getMetadata($key = null)
    {
        return $key === null ? [] : null;
    }

    public function getContents()
    {
        $ret = '';
        while (! $this->eof()) {
            $ret .= $this->read(PHP_INT_MAX);
        }
        return $ret;
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function getSize()
    {
        return null;
    }

    public function tell()
    {
        throw new BadMethodCallException('Cannot tell a GeneratorStream.');
    }

    public function isReadable()
    {
        return true;
    }

    public function read($length)
    {
        $ret = (string) $this->generator->current();
        $this->generator->next();
        return $ret;
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new BadMethodCallException('Cannot seek a GeneratorStream.');
    }

    public function isWritable()
    {
        return false;
    }

    public function write($string)
    {
        throw new BadMethodCallException('Cannot write a GeneratorStream.');
    }
}