<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Http;

use Psr\Http\Message\StreamInterface;

interface CodingInterface {
    
    public function getHttpName(): string;
    
    public function hasEncodingFilter(): bool;
    
    public function encodeStream(StreamInterface $stream): StreamInterface;
    
    public function hasDecodingFilter(): bool;
    
    public function decodeStream(StreamInterface $stream): StreamInterface;
}

