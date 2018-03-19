<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;
use Slothsoft\Core\IO\Writable\FileWriterInterface;
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

    public function getLinkedStylesheets(): array;

    public function getLinkedScripts(): array;
}

