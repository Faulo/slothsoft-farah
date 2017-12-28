<?php
/***********************************************************************
 * Slothsoft\Farah\ResourceFont v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

class ResourceFont extends Resource
{

    protected static $plattformIdCodes = array(
        0 => 'Unicode',
        1 => 'Macintosh',
        3 => 'Microsoft'
    );

    protected static $nameIdCodes = array(
        0 => 'copyright',
        1 => 'family',
        4 => 'name',
        5 => 'version',
        6 => 'postscript',
        8 => 'manufacturer'
    );

    protected function unpackShort($pos)
    {
        $ret = unpack('n', substr($this->fileData, $pos, 2));
        return reset($ret);
    }

    protected function unpackLong($pos)
    {
        $ret = unpack('N', substr($this->fileData, $pos, 4));
        return reset($ret);
    }

    protected function loadFileXML()
    {
        $ret = array();
        $data = $this->getContent();
        
        $tableCount = $this->unpackShort(4);
        
        for ($i = 0; $i < $tableCount; $i ++) {
            $tag = substr($this->fileData, 16 * $i + 12, 4);
            if ($tag === 'name') {
                $tableOffset = $this->unpackLong(16 * $i + 20);
                $recordCount = $this->unpackShort($tableOffset + 2);
                $storageOffset = $this->unpackShort($tableOffset + 4) + $tableOffset;
                
                for ($j = 0; $j < $recordCount; $j ++) {
                    $recordOffset = $tableOffset + 12 * $j + 6;
                    
                    $platformId = $this->unpackShort($recordOffset + 0);
                    $encodingId = $this->unpackShort($recordOffset + 2);
                    $languageId = $this->unpackShort($recordOffset + 4);
                    $nameId = $this->unpackShort($recordOffset + 6);
                    $nameLength = $this->unpackShort($recordOffset + 8);
                    $nameOffset = $this->unpackShort($recordOffset + 10);
                    if (isset(self::$nameIdCodes[$nameId])) {
                        $name = substr($this->fileData, $storageOffset + $nameOffset, $nameLength);
                        $en = false;
                        switch ($platformId) {
                            case 0: // Unicode UTF-16
                                $name = mb_convert_encoding($name, 'UTF-8', 'UTF-16');
                                $en = true;
                                break;
                            case 1: // Macintosh
                                $name = utf8_encode($name);
                                $en = $languageId === 0;
                            case 3: // Windows
                                switch ($encodingId) {
                                    case 0: // Symbol
                                        break;
                                    case 1: // Unicode BMP (UCS-2)
                                        $name = mb_convert_encoding($name, 'UTF-8', 'UCS-2');
                                        break;
                                    case 10:
                                        $name = mb_convert_encoding($name, 'UTF-8', 'UCS-4');
                                        break;
                                    default: // ShiftJIS PRC Big5 Wansung Johab Reserved Reserved Reserved
                                        $name = utf8_encode($name);
                                        break;
                                }
                                $en = $languageId === 0x409;
                                break;
                        }
                        if ($en) {
                            $ret[self::$nameIdCodes[$nameId]] = $name;
                        }
                    }
                }
            }
        }
        if (isset($ret['name'])) {
            $root = $this->value2dom($this->resDoc, $ret);
            $this->resNode->appendChild($root);
        }
    }
}
