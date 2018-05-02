<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use GuzzleHttp\Psr7\PumpStream;
use Psr\Http\Message\StreamInterface;
use Slothsoft\Core\Calendar\Seconds;
use Slothsoft\Core\IO\HTTPStream;
use Slothsoft\Farah\Exception\StreamTimedOutException;
use Exception;
use Generator;

/**
 *
 * @author Daniel Schulz
 *        
 */
class HttpStreamResult extends ResultBase 
{
    private $stream;
    public function __construct(HTTPStream $stream) {
        $this->stream = $stream;
    }
    
    public function lookupStream() : StreamInterface
    {
        return new PumpStream($this->yieldContent());
    }
    
    public function lookupMimeType() : string
    {
        return $this->stream->getMime();
    }
    
    public function lookupCharset() : string
    {
        return $this->stream->getEncoding();
    }
    
    public function lookupFileName() : string
    {
        return 'null.txt';
    }
    
    public function lookupChangeTime() : int {
        return 0;
    }
    
    public function lookupHash() : string {
        return '';
    }
    
    private function yieldContent() : Generator {
        yield '';
        $intervalTime = 0;
        $timeoutTime = 0;
        $sleepDuration = $this->stream->getSleepDuration();
        $heartbeatContent = $this->stream->getHeartbeatContent();
        $heartbeatInterval = $this->stream->getHeartbeatInterval();
        $heartbeatTimeout = $this->stream->getHeartbeatTimeout();
        $heartbeatEOL = $this->stream->getHeartbeatEOL();
        $heartbeatSent = false;
        while (! connection_aborted()) {
            try {
                $status = $this->stream->getStatus();
                if ($timeoutTime > $heartbeatTimeout) {
                    throw new StreamTimedOutException(get_class($this->stream));
                }
            } catch (Exception $e) {
                $status = HTTPStream::STATUS_ERROR;
            }
            switch ($status) {
                case HTTPStream::STATUS_CONTENTDONE:
                    $content = $this->stream->getContent();
                    if ($content !== '') {
                        if ($heartbeatSent and $heartbeatEOL !== null) {
                            yield $heartbeatEOL;
                            $heartbeatSent = false;
                        }
                        yield $content;
                    }
                    // fallthrouuugh /o/
                case HTTPStream::STATUS_DONE:
                case HTTPStream::STATUS_ERROR:
                    break 2;
                case HTTPStream::STATUS_CONTENT:
                    $content = $this->stream->getContent();
                    if ($content !== '') {
                        if ($heartbeatSent and $heartbeatEOL !== null) {
                            yield $heartbeatEOL;
                            $heartbeatSent = false;
                        }
                        $intervalTime = 0;
                        $timeoutTime = 0;
                        yield $content;
                    }
                    break;
                case HTTPStream::STATUS_RETRY:
                    $intervalTime += $sleepDuration;
                    $timeoutTime += $sleepDuration;
                    usleep($sleepDuration * Seconds::USLEEP_FACTOR);
                    if ($intervalTime > $heartbeatInterval and $heartbeatContent !== null) {
                        $intervalTime = 0;
                        yield $heartbeatContent;
                        $heartbeatSent = true;
                    }
                    break;
            }
        }
        return false;
    }
}

