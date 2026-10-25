<?php
declare(strict_types = 1);

namespace Slothsoft\Farah\ResponseStrategy;

use Psr\Http\Message\ResponseInterface;
use Slothsoft\Core\IO\Memory;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Http\StreamingResponse;

/**
 * Response strategy that emits only the response body.
 *
 * @author Daniel Schulz
 * @since 2018-04-17
 */
final readonly class SendBodyStrategy implements ResponseStrategyInterface {
    
    private const HTTP_OUTPUT = 'php://output';
    
    private string $destination;
    
    private bool $isHttpOutput;
    
    private int $chunkSize;
    
    public function __construct(?string $destination = null, ?int $chunkSize = null) {
        $this->destination = $destination ?? self::HTTP_OUTPUT;
        $this->isHttpOutput = $this->destination === self::HTTP_OUTPUT;
        $this->chunkSize = $chunkSize ?? Memory::ONE_KILOBYTE;
    }
    
    public function process(ResponseInterface $response): void {
        $input = $response->getBody();
        $output = fopen($this->destination, StreamWrapperInterface::MODE_CREATE_WRITEONLY);
        
        if (is_resource($output)) {
            try {
                $streaming = $response instanceof StreamingResponse;
                while (! $input->eof()) {
                    fwrite($output, $input->read($this->chunkSize));
                    if ($streaming) {
                        if ($this->isHttpOutput) {
                            $this->flushHttpOutput();
                        } else {
                            fflush($output);
                        }
                    }
                }
            } finally {
                fclose($output);
                $input->close();
            }
        }
    }
    
    private function flushHttpOutput(): void {
        $status = ob_get_status();
        if ($status && ($status['flags'] & PHP_OUTPUT_HANDLER_FLUSHABLE)) {
            ob_flush();
        }
        flush();
    }
}
