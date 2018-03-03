<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Farah\Module\AssetUses\DOMWriterInterface;
use Slothsoft\Farah\Module\AssetUses\FileWriterInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ResultInterface extends DOMWriterInterface, FileWriterInterface
{

    public function __toString(): string;

    public function getUrl(): FarahUrl;

    public function getId(): string;

    public function getArguments(): FarahUrlArguments;

    public function exists(): bool;
}

