<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Event\Events;

/**
 *
 * @author Daniel Schulz
 *        
 */
class SetParameterEvent extends GenericEvent
{

    private $name;

    private $value;

    public function initEvent(string $type, array $options)
    {
        parent::initEvent($type, $options);
        
        $this->name = $options['name'] ?? '';
        $this->value = $options['value'] ?? '';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

