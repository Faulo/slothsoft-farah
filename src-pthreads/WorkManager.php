<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\PThreads;

use Slothsoft\Core\IO\Writable\ChunkWriterInterface;
use Generator;
use Throwable;

class WorkManager implements ChunkWriterInterface {
    
    private $treadCount;
    
    private $instructionPiles;
    
    public function __construct(int $treadCount) {
        $this->treadCount = $treadCount;
        $this->instructionPiles = [];
    }
    
    public function thenDo(string $className, array $options = []): self {
        $this->instructionPiles[count($this->instructionPiles) - 1][] = new WorkInstruction($className, $options);
        return $this;
    }
    
    public function thenWait(): self {
        $this->instructionPiles[] = [];
        return $this;
    }
    
    public function toChunks(): Generator {
        try {
            $logger = new WorkEntries();
            $instructions = new WorkEntries();
            $pool = new WorkPool($this->treadCount, WorkWorker::class, [
                $logger,
                $instructions
            ]);
            
            foreach ($this->instructionPiles as $instructionPile) {
                foreach ($instructionPile as $instruction) {
                    $instructions->append($instruction);
                }
                
                do {
                    foreach ($instructions->fetch() as $instruction) {
                        $pool->submitWork($instruction);
                    }
                    
                    while ($pool->hasWork()) {
                        yield from $logger->fetch();
                    }
                    
                    yield from $logger->fetch();
                } while ($instructions->hasEntries());
            }
            
            $pool->shutdown();
            unset($pool);
        } catch (Throwable $e) {
            yield (string) $e;
        }
    }
}

