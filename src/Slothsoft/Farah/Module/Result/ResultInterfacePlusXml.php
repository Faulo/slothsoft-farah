<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Module\Result;

use Slothsoft\Core\IO\Writable\DOMWriterInterface;

interface ResultInterfacePlusXml extends ResultInterface, DOMWriterInterface
{
}

