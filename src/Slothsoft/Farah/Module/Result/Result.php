<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\FarahUrl\FarahUrlStreamIdentifier;
use Slothsoft\Farah\Module\Executable\ExecutableInterface;

class Result implements ResultInterface
{

    private $ownerExecutable;

    private $type;

    private $strategies;

    private $hash;

    private $mimeType;

    private $charset;

    private $fileName;

    private $changeTime;

    private $isBufferable;

    public function __construct(ExecutableInterface $ownerExecutable, FarahUrlStreamIdentifier $type, ResultStrategies $strategies)
    {
        $this->ownerExecutable = $ownerExecutable;
        $this->type = $type;
        $this->strategies = $strategies;
    }

    public function createUrl(): FarahUrl
    {
        return $this->ownerExecutable->createUrl($this->type);
    }

    public function lookupStream(): StreamInterface
    {
        return $this->strategies->streamBuilder->buildStream($this);
    }

    public function lookupHash(): string
    {
        if ($this->hash === null) {
            $this->hash = $this->strategies->streamBuilder->buildStreamHash($this);
        }
        return $this->hash;
    }

    public function lookupMimeType(): string
    {
        if ($this->mimeType === null) {
            $this->mimeType = $this->strategies->streamBuilder->buildStreamMimeType($this);
        }
        return $this->mimeType;
    }

    public function lookupCharset(): string
    {
        if ($this->charset === null) {
            $this->charset = $this->strategies->streamBuilder->buildStreamCharset($this);
        }
        return $this->charset;
    }

    public function lookupIsBufferable(): bool
    {
        if ($this->isBufferable === null) {
            $this->isBufferable = $this->strategies->streamBuilder->buildStreamIsBufferable($this);
        }
        return $this->isBufferable;
    }

    public function lookupFileName(): string
    {
        if ($this->fileName === null) {
            $this->fileName = $this->strategies->streamBuilder->buildStreamFileName($this);
        }
        return $this->fileName;
    }

    public function lookupChangeTime(): int
    {
        if ($this->changeTime === null) {
            $this->changeTime = $this->strategies->streamBuilder->buildStreamChangeTime($this);
        }
        return $this->changeTime;
    }
}

