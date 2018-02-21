<?php
namespace Slothsoft\Farah\Stream;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface OutputStreamInterface
{

    public function stream_write(string $data): int;
}

