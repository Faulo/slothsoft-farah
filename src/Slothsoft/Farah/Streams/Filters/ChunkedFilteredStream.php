<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Streams\Filters;

class ChunkedFilteredStream extends FilteredStreamBase {
    protected function processHeader(): string
    {
        return '';
    }

    protected function processPayload(string $data): string
    {
        return $data === ''
            ? ''
            : dechex(strlen($data)) . "\r\n" . $data . "\r\n";
    }

    protected function processFooter(): string
    {
        return "0\r\n\r\n";
    }
}
