<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results\Files;

use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class XmlFileResult extends FileResult
{

    /**
     *
     * {@inheritdoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper(): StreamWrapperInterface
    {
        return $this->createDefaultStreamWrapper();
    }
}

