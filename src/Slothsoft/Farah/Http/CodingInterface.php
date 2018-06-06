<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\IO\Writable\FilteredStreamWriterInterface;

interface CodingInterface
{

    public function __toString(): string;

    public function isNoEncoding(): bool;

    public function setEncodingFilter(FilteredStreamWriterInterface $encodingWriter);

    public function clearEncodingFilter();

    public function hasEncodingFilter(): bool;

    public function encodeStream(StreamInterface $stream): StreamInterface;

    public function setDecodingFilter(FilteredStreamWriterInterface $decodingWriter);

    public function clearDecodingFilter();

    public function hasDecodingFilter(): bool;

    public function decodeStream(StreamInterface $stream): StreamInterface;
}

