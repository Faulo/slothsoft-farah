<?php
declare(strict_types = 1);
/**
 * *********************************************************************
 * Slothsoft\Farah\HTTPStream v1.00 28.05.2014 � Daniel Schulz
 *
 * Changelog:
 * v1.00 28.05.2014
 * initial release
 * *********************************************************************
 */
namespace Slothsoft\Farah;

abstract class HTTPStream
{

    const STATUS_ERROR = 0;

    const STATUS_DONE = 1;

    const STATUS_RETRY = 2;

    const STATUS_CONTENT = 3;

    const STATUS_CONTENTDONE = 4;

    protected $headerList = [];

    protected $status = self::STATUS_DONE;

    protected $content = '';

    protected $mime = null;

    protected $encoding = null;

    protected $sleepDuration = TIME_MILLISECOND;

    protected $heartbeatContent = null;

    // character to send after each heartbeatInterval is reached
    protected $heartbeatEOL = null;

    // character to send before new content, when heartbeatContent was sent
    protected $heartbeatInterval = TIME_SECOND;

    // time without actual content before heartbeatContent is sent
    protected $heartbeatTimeout = TIME_HOUR;

    // time without actual content before child is terminated
    public function getMime()
    {
        return $this->mime;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function getHeaderList()
    {
        return $this->headerList;
    }

    public function getStatus()
    {
        $this->parseStatus();
        return $this->status;
    }

    public function setStatusError()
    {
        $this->status = self::STATUS_ERROR;
    }

    public function setStatusDone()
    {
        $this->status = self::STATUS_DONE;
    }

    public function setStatusRetry()
    {
        $this->status = self::STATUS_RETRY;
    }

    public function setStatusContent()
    {
        $this->status = self::STATUS_CONTENT;
    }

    public function setStatusContentDone()
    {
        $this->status = self::STATUS_CONTENTDONE;
    }

    protected function parseStatus()
    {}

    public function getContent()
    {
        $this->parseContent();
        return $this->content;
    }

    protected function parseContent()
    {}

    public function getSleepDuration()
    {
        return $this->sleepDuration;
    }

    public function getHeartbeatInterval()
    {
        return $this->heartbeatInterval;
    }

    public function getHeartbeatTimeout()
    {
        return $this->heartbeatTimeout;
    }

    public function getHeartbeatContent()
    {
        return $this->heartbeatContent;
    }

    public function getHeartbeatEOL()
    {
        return $this->heartbeatEOL;
    }

    public function __toString()
    {
        return sprintf('%s: %d', __CLASS__, time());
    }
}