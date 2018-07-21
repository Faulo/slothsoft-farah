<?php
namespace Slothsoft\Farah\PThreads;

use Threaded;
use Throwable;

abstract class AbstractWorkThread extends Threaded
{
    abstract protected function work(): void;
    
    private $options;
    
    public function __construct(array $options) {
        $this->options = $options;
    }
    
    public function run() : void {
        try {
            $this->work();
        } catch(Throwable $e) {
            $this->log(get_class($e) . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString(), true);
        }
    }
    
    protected function getOptions() : array {
        return (array) $this->options;
    }
    protected function getOption(string $key) {
        return $this->options[$key] ?? null;
    }
    protected function log($message, bool $isImportant = false) : void {
        if (! is_string($message)) {
            $message = print_r($message, true);
        }
        $isImportant = $isImportant ? '!!!' : '   ';
        $message = sprintf('[%s] %s %s: %s %s', date('d.m.y H:i:s'), $isImportant, basename(get_class($this)), $message, PHP_EOL);
        $this->worker->logger->append($message);
    }
    protected function thenDo(string $className, array $options = []) : void {
        $this->worker->instructions->append(new WorkInstruction($className, $options));
    }
}

