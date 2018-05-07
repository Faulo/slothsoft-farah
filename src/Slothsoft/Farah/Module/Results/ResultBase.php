<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Farah\Module\Executables\ExecutableInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlStreamIdentifier;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class ResultBase implements ResultInterface
{

    private $ownerExecutable;

    private $type;

    public function init(ExecutableInterface $ownerExecutable, FarahUrlStreamIdentifier $type)
    {
        $this->ownerExecutable = $ownerExecutable;
        $this->type = $type;
    }

    public function getId(): string
    {
        return (string) $this->createUrl();
    }

    public function createUrl(): FarahUrl
    {
        return $this->ownerExecutable->createUrl($this->type);
    }
}

