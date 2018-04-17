<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;

class SendBodyStrategy extends ResponseStrategyBase
{
    private $destination;
    
    public function __construct(string $destination = 'php://output') {
        $this->destination = $destination;
    }

    public function process(ResponseInterface $response)
    {
        $input = $response->getBody()->detach();
        $output = fopen($this->destination, StreamWrapperInterface::MODE_CREATE_WRITEONLY);
        
        if (is_resource($input) and is_resource($output)) {
            stream_copy_to_stream($input, $output);
        }
    }
}

