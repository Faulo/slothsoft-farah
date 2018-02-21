<?php
namespace Slothsoft\Farah\Stream;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface InputStreamInterface
{

    public function stream_read(int $count): string;

    public function stream_tell(): int;

    public function stream_eof(): bool;

    public function stream_seek(int $offset, int $whence): int;
}

