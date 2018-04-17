<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use Slothsoft\Farah\StreamWrapper\StringStreamWrapper;

/**
 *
 * @author Daniel Schulz
 *        
 */
class NullResult extends ResultImplementation
{
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadDefaultStreamWrapper()
     */
    protected function loadDefaultStreamWrapper() : StreamWrapperInterface
    {
        return new StringStreamWrapper('');
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultImplementation::loadXmlStreamWrapper()
     */
    protected function loadXmlStreamWrapper() : StreamWrapperInterface
    {
        return new StringStreamWrapper('<null/>');
    }
    /**
     * {@inheritDoc}
     * @see \Slothsoft\Farah\Module\Results\ResultInterface::exists()
     */
    public function exists(): bool
    {
        return false;
    }

}

