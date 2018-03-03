<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Farah\Module\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\FarahUrl\FarahUrlArguments;

/**
 *
 * @author Daniel Schulz
 *        
 */
abstract class GenericResult implements ResultInterface
{

    private $url;

    public function __construct(FarahUrl $url)
    {
        $this->url = $url;
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getUrl(): FarahUrl
    {
        return $this->url;
    }

    public function getId(): string
    {
        return (string) $this->url;
    }

    public function getArguments(): FarahUrlArguments
    {
        return $this->url->getArguments();
    }
}

