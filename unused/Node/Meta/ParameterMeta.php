<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Node\Meta;

use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Module\Node\Instruction\ParameterInstructionInterface;

/**
 *
 * @author Daniel Schulz
 *        
 */
class ParameterMeta extends MetaBase implements ParameterInstructionInterface
{

    public function isParameter(): bool
    {
        return true;
    }

    public function getParameterName(): string
    {
        return $this->getElementAttribute(Module::ATTR_PARAM_KEY);
    }

    public function getParameterValue(): string
    {
        return $this->getElementAttribute(Module::ATTR_PARAM_VAL, '');
    }
}
