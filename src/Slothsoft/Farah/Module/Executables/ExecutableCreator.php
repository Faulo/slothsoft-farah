<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Executables;

use Psr\Http\Message\MessageInterface;
use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Farah\Module\Executables\Files\BinaryFile;
use Slothsoft\Farah\Module\Executables\Files\HtmlFile;
use Slothsoft\Farah\Module\Executables\Files\XmlFile;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\InstructionCollector;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;

class ExecutableCreator
{
    private $ownerAsset;
    private $type;
    public function __construct(AssetInterface $ownerAsset, FarahUrlArguments $args) {
        $this->ownerAsset = $ownerAsset;
        $this->args = $args;
    }
    
    protected function initExecutable(ExecutableInterface $executable) : ExecutableInterface {
        $executable->init($this->ownerAsset, $this->args);
        return $executable;
    }
    
    public function createNullExecutable() : ExecutableInterface {
        return $this->initExecutable(new NullExecutable());
    }
    
    public function createDOMWriterExecutable(DOMWriterInterface $writer) : ExecutableInterface {
        return $this->initExecutable(new DOMWriterExecutable($writer));
    }
    
    public function createMessageExecutable(MessageInterface $message) : ExecutableInterface {
        return $this->initExecutable(new NullExecutable());
    }
    
    public function createTransformationExecutable(string $name, InstructionCollector $collector) : ExecutableInterface {
        return $this->initExecutable(new TransformationExecutable($name, $collector));
    }
    
    public function createXmlFile(string $pathToFile) : ExecutableInterface {
        return $this->initExecutable(new XmlFile($pathToFile));
    }
    
    public function createHtmlFile(string $pathToFile) : ExecutableInterface {
        return $this->initExecutable(new HtmlFile($pathToFile));
    }
    
    public function createBinaryFile(string $pathToFile) : ExecutableInterface {
        return $this->initExecutable(new BinaryFile($pathToFile));
    }
    
    
}

