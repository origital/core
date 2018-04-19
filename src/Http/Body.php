<?php

/**
 * Origital Core
 *
 * @author Carlos Acedo <carlos@origital.com>
 */

namespace Origital\Http;

use Origital\Http\Exception\BodyException;

class Body
{
    private $resource;
    private $stream;

    static public function assertInstance($body, $mode)
    {
        if ($body instanceof Body) {
            return $body;
        }
        if (!is_string($body) && !is_resource($body)) {
            throw new BodyException(
                'Body must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Origital\Http\Body object'
            );
        }
        return new Body($body, $mode);
    }

    public function __construct($stream, $mode = 'r')
    {
        $this->setStream($stream, $mode);
    }

    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }
            return $this->getContents();
        } catch (BodyException $e) {
            return '';
        }
    }

    public function close(): Body
    {
        if ($this->resource === null) {
            return $this;
        }
        $resource = $this->detach();
        fclose($resource);
        return $this;
    }

    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function attach($resource, $mode = 'r'): Body
    {
        return $this->setStream($resource, $mode);
    }

    public function getSize()
    {
        if (null === $this->resource) {
            return null;
        }
        $stats = fstat($this->resource);
        return $stats['size'];
    }

    public function isEmpty()
    {
        $size = $this->getSize();
        return $size === null || $size > 0;
    }

    public function tell()
    {
        if ($this->resource === null) {
            throw new BodyException('No resource available; cannot tell position');
        }
        $result = ftell($this->resource);
        if (!is_int($result)) {
            throw new BodyException('Error occurred during tell operation');
        }
        return $result;
    }

    public function eof()
    {
        if ($this->resource === null) {
            return true;
        }
        return feof($this->resource);
    }

    public function isSeekable()
    {
        if ($this->resource === null) {
            return false;
        }
        $meta = stream_get_meta_data($this->resource);
        return $meta['seekable'] ?? false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if ($this->resource === null) {
            throw new BodyException('No resource available; cannot seek position');
        }
        if (!$this->isSeekable()) {
            throw new BodyException('Stream is not seekable');
        }
        $result = fseek($this->resource, $offset, $whence);
        if ($result !== 0) {
            throw new BodyException('Error seeking within stream');
        }
        return true;
    }

    public function rewind()
    {
        return $this->seek(0);
    }

    public function isWritable()
    {
        if ($this->resource === null) {
            return false;
        }
        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];
        return (
               strstr($mode, 'x') !== false
            || strstr($mode, 'w') !== false
            || strstr($mode, 'c') !== false
            || strstr($mode, 'a') !== false
            || strstr($mode, '+') !== false
        );
    }

    public function write($string)
    {
        if ($this->resource === null) {
            throw new BodyException('No resource available; cannot write');
        }
        if (!$this->isWritable()) {
            throw new BodyException('Stream is not writable');
        }
        $result = fwrite($this->resource, $string);
        if ($result === false) {
            throw new BodyException('Error writing to stream');
        }
        return $result;
    }

    public function truncate(int $size = 0)
    {
        if ($this->resource === null) {
            throw new BodyException('No resource available; cannot truncate');
        }
        if (!$this->isWritable()) {
            throw new BodyException('Stream is not truncable');
        }
        $result = ftruncate($this->resource, $size);
        if ($result === false) {
            throw new BodyException('Error truncating the stream');
        }
        return $this;
    }

    public function isReadable()
    {
        if ($this->resource === null) {
            return false;
        }
        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];
        return strstr($mode, 'r') !== false || strstr($mode, '+') !== false;
    }

    public function read(int $length)
    {
        if ($this->resource === null) {
            throw new BodyException('No resource available; cannot read');
        }
        if (!$this->isReadable()) {
            throw new BodyException('Stream is not readable');
        }
        $result = fread($this->resource, $length);
        if ($result === false) {
            throw new BodyException('Error reading stream');
        }
        return $result;
    }

    public function getContents()
    {
        if (!$this->isReadable()) {
            throw new BodyException('Stream is not readable');
        }
        $result = stream_get_contents($this->resource);
        if ($result === false) {
            throw new BodyException('Error reading from stream');
        }
        return $result;
    }

    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->resource);
        if (null === $key) {
            return $metadata;
        }
        return $metadata[$key] ?? null;
    }

    private function setStream($stream, $mode = 'r'): Body
    {
        $error    = null;
        $resource = $stream;
        if (is_string($stream)) {
            set_error_handler(function ($e) use (&$error) {
                $error = $e;
            }, E_WARNING);
            $resource = fopen($stream, $mode);
            restore_error_handler();
        }
        if ($error) {
            throw new BodyException('Invalid stream reference provided');
        }
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new BodyException(
                'Invalid stream provided; must be a string stream identifier or stream resource'
            );
        }
        if ($stream !== $resource) {
            $this->stream = $stream;
        }
        $this->resource = $resource;
        return $this;
    }
}
