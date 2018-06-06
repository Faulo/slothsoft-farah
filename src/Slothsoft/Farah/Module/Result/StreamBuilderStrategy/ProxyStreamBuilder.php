<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;

class ProxyStreamBuilder implements StreamBuilderStrategyInterface
{

    private $proxy;

    public function __construct(ResultInterface $proxy)
    {
        $this->proxy = $proxy;
    }

    public function buildStream(ResultInterface $context): StreamInterface
    {
        return $this->proxy->lookupStream();
    }

    public function buildStreamMimeType(ResultInterface $context): string
    {
        return $this->proxy->lookupMimeType();
    }

    public function buildStreamCharset(ResultInterface $context): string
    {
        return $this->proxy->lookupCharset();
    }

    public function buildStreamFileName(ResultInterface $context): string
    {
        return $this->proxy->lookupFileName();
    }

    public function buildStreamChangeTime(ResultInterface $context): int
    {
        return $this->proxy->lookupChangeTime();
    }

    public function buildStreamHash(ResultInterface $context): string
    {
        return $this->proxy->lookupHash();
    }

    public function buildStreamIsBufferable(ResultInterface $context): bool
    {
        return $this->proxy->lookupIsBufferable();
    }
}

