<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\Exception;

use RuntimeException;
use Slothsoft\Farah\Module\Result\ResultInterface;

/**
 * Exception type to force an HTTP download response.
 *
 * @author Daniel Schulz
 * @since 2018-06-27
 */
final class HttpDownloadException extends RuntimeException {
    
    private ResultInterface $result;
    
    private bool $inline;
    
    public function __construct(ResultInterface $result, bool $isInline = false) {
        parent::__construct();
        $this->result = $result;
        $this->inline = $isInline;
    }
    
    public function getResult(): ResultInterface {
        return $this->result;
    }
    
    public function isInline(): bool {
        return $this->inline;
    }
}

