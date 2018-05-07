<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;

class SendBodyStrategy extends ResponseStrategyBase
{

    private $destination;

    private $chunkSize;

    public function __construct(string $destination, int $chunkSize)
    {
        $this->destination = $destination;
        $this->chunkSize = $chunkSize;
    }

    public function process(ResponseInterface $response)
    {
        $input = $response->getBody();
        $output = fopen($this->destination, StreamWrapperInterface::MODE_CREATE_WRITEONLY);
        
        if (is_resource($output)) {
            while (!$input->eof()) {
                fwrite($output, $input->read($this->chunkSize));
            }
            $input->close();
            fclose($output);
        }
    }
}

