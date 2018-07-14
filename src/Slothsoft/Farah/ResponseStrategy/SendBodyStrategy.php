<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;
use Slothsoft\Core\IO\Memory;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;

class SendBodyStrategy extends ResponseStrategyBase
{

    private $destination;

    private $chunkSize;

    public function __construct(?string $destination = null, ?int $chunkSize = null)
    {
        $this->destination = $destination ?? 'php://output';
        $this->chunkSize = $chunkSize ?? Memory::ONE_KILOBYTE;
    }

    public function process(ResponseInterface $response)
    {
        $input = $response->getBody();
        $output = fopen($this->destination, StreamWrapperInterface::MODE_CREATE_WRITEONLY);
        
        if (is_resource($output)) {
            while (! $input->eof()) {
                fwrite($output, $input->read($this->chunkSize));
            }
            $input->close();
            fclose($output);
        }
    }
}

