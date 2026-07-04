<?php
declare(strict_types = 1);

namespace Slothsoft\Farah;

/**
 * Legacy event object used by the root-level HTTP command and response classes.
 *
 * @author Daniel Schulz
 * @since 2017-12-28
 * @deprecated Included for historical compatibility only. This API is deprecated and should not be used in new code.
 */
final class HTTPEvent {
    
    // implements https://dom.spec.whatwg.org/#interface-event
    public $type;
    
    public $target;
    
    public $data;
    
    public $timeStamp;
    
    public function __construct($type, array $eventInit = []) {
        $this->type = $type;
        $this->timeStamp = time();
    }
}