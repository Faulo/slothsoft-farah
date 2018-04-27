<?php
namespace Slothsoft\Farah\Module\Executables;

use Slothsoft\Farah\Module\Executables\Files\BinaryFile;
use Slothsoft\Farah\Module\Executables\Files\HtmlFile;
use Slothsoft\Farah\Module\Executables\Files\XmlFile;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;
use Slothsoft\Farah\Module\Node\InstructionCollector;
use Slothsoft\Farah\Module\Node\Asset\AssetInterface;
use Psr\Http\Message\MessageInterface;

class ExecutableCreator
{
    protected $ownerAsset;
    protected $type;
    public function __construct(AssetInterface $ownerAsset, FarahUrlArguments $args) {
        $this->ownerAsset = $ownerAsset;
        $this->args = $args;
    }
    
    public function createNullExecutable() : ExecutableInterface {
        $executable = new NullExecutable();
        $executable->init($this->ownerAsset, $this->args);
        return $executable;
    }
    
    public function createMessageExecutable(MessageInterface $message) : ExecutableInterface {
        $executable = new NullExecutable();
        $executable->init($this->ownerAsset, $this->args);
        return $executable;
    }
    
    public function createTransformationExecutable(string $name, InstructionCollector $collector) : ExecutableInterface {
        $executable = new TransformationExecutable($name, $collector);
        $executable->init($this->ownerAsset, $this->args);
        return $executable;
    }
    
    public function createXmlFile(string $pathToFile) : ExecutableInterface {
        $executable = new XmlFile($pathToFile);
        $executable->init($this->ownerAsset, $this->args);
        return $executable;
    }
    
    public function createHtmlFile(string $pathToFile) : ExecutableInterface {
        $executable = new HtmlFile($pathToFile);
        $executable->init($this->ownerAsset, $this->args);
        return $executable;
    }
    
    public function createBinaryFile(string $pathToFile) : ExecutableInterface {
        $executable = new BinaryFile($pathToFile);
        $executable->init($this->ownerAsset, $this->args);
        return $executable;
    }
    
    
}

