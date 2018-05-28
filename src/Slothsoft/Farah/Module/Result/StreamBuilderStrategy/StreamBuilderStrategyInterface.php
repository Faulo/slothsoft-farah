<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result\StreamBuilderStrategy;

use Psr\Http\Message\StreamInterface;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface StreamBuilderStrategyInterface
{

    /**
     * Create a stream of this result.
     *
     * @return StreamInterface
     */
    public function buildStream(ResultInterface $context): StreamInterface;

    /**
     * Determine the filename of this result.
     *
     * @return string
     */
    public function buildStreamFileName(ResultInterface $context): string;

    /**
     * Determine the mime type of this result.
     *
     * @return string
     */
    public function buildStreamMimeType(ResultInterface $context): string;

    /**
     * Determine the character set of this result
     *
     * @return string A valid character, or the empty string if not applicable.
     */
    public function buildStreamCharset(ResultInterface $context): string;

    /**
     * Determine the time of the last change to this resource, or 0 if undeterminable
     *
     * @return int
     */
    public function buildStreamChangeTime(ResultInterface $context): int;

    /**
     * Determine a hash for this resource, or the empty string if undeterminable
     *
     * @return string
     */
    public function buildStreamHash(ResultInterface $context): string;

    /**
     * Determine whether or not intermediate filters are allowed to buffer this result, or should always flush immediately.
     *
     * @return bool
     */
    public function buildStreamIsBufferable(ResultInterface $context): bool;
}
