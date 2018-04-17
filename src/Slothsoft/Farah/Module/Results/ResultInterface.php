<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;

/**
 *
 * @author Daniel Schulz
 *        
 */
interface ResultInterface 
{
    const STREAM_TYPE_DEFAULT = '';
    const STREAM_TYPE_XML = 'xml';

    public function __toString(): string;

    public function getUrl(): FarahUrl;

    public function getId(): string;

    public function getArguments(): FarahUrlArguments;

    public function exists(): bool;

    public function getLinkedStylesheets(): array;

    public function getLinkedScripts(): array;
    
    public function createDefaultUrl() : FarahUrl;
    public function createDefaultStreamWrapper() : StreamWrapperInterface;
    
    public function createXmlUrl() : FarahUrl;
    public function createXmlStreamWrapper() : StreamWrapperInterface;
}

