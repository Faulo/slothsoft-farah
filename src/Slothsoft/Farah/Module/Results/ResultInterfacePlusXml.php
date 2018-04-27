<?php
namespace Slothsoft\Farah\Module\Results;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;

interface ResultInterfacePlusXml extends ResultInterface, DOMWriterInterface
{
}

