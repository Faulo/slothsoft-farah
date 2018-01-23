<?php 
// © 2012 Daniel Schulz
namespace Slothsoft\Farah;

use Slothsoft\Core\DOMHelper;
use Slothsoft\Minecraft\NBT\TAGNode;

class ResourceMCRegion extends Resource
{

    protected function unpackShort($data)
    {
        $ret = unpack('n', str_pad($data, 2, chr(0), STR_PAD_LEFT));
        return reset($ret);
    }

    protected function unpackLong($data)
    {
        $ret = unpack('N', str_pad($data, 4, chr(0), STR_PAD_LEFT));
        return reset($ret);
    }

    protected function loadFileXML()
    {
        $this->fileHandle = fopen($this->getPath(), 'rb');
        $tooNot = array();
        $tooFar = array();
        $tooLong = array();
        $chunkNodes = array();
        $imgStr = '';
        for ($i = 0; $i < 16; $i ++) {
            // Chunks per File
            $pos = $i << 2;
            fseek($this->fileHandle, $pos, SEEK_SET);
            
            $offset = $this->unpackLong(fread($this->fileHandle, 3));
            $sector = $this->unpackShort(fread($this->fileHandle, 1));
            
            $pos = $pos + 4096;
            fseek($this->fileHandle, $pos, SEEK_SET);
            $timestamp = $this->unpackLong(fread($this->fileHandle, 4));
            
            if ($offset and $timestamp) {
                $pos = $offset << 12;
                if ($pos > 0) {
                    fseek($this->fileHandle, $pos, SEEK_SET);
                    $length = $this->unpackLong(fread($this->fileHandle, 4));
                    if ($length > 0) {
                        $compression = $this->unpackShort(fread($this->fileHandle, 1));
                        $data = fread($this->fileHandle, $length - 1);
                        
                        switch ($compression) {
                            case 1:
                                $data = gzdeflate($data);
                                break;
                            case 2:
                                $data = gzuncompress($data);
                                break;
                        }
                        $tagNode = TAGNode::createDocument($data);
                        header('Content-Type: text/plain');
                        my_dump($tagNode);
                        die();
                        $domNode = TAGNode::TAG2DOM($this->resDoc, $tagNode);
                        
                        if ($blockNode = $tagNode->getElementsByName('Blocks')) {
                            $blockNode = reset($blockNode);
                            $map = array();
                            for ($p = 0, $x = 0; $x < 16; $x ++) {
                                if (! isset($map[$x])) {
                                    $map[$x] = array();
                                }
                                for ($z = 0; $z < 16; $z ++) {
                                    if (! isset($map[$x][$z])) {
                                        $map[$x][$z] = array();
                                    }
                                    for ($y = 127; $y >= 0; $y --, $p ++) {
                                        $map[$x][$z][$y] = unpack('C', $blockNode->Payload['bytes'][$p]);
                                        $map[$x][$z][$y] = reset($map[$x][$z][$y]);
                                    }
                                }
                            }
                            // *
                            $row = array();
                            foreach ($map as $rows) {
                                $row[] = reset($rows);
                            }
                            $pic = $this->drawRow($row, 4);
                            ob_start();
                            imagepng($pic);
                            $imgbinary = ob_get_clean();
                            
                            $imgNode = $this->resDoc->createElementNS(DOMHelper::NS_HTML, 'img');
                            $imgNode->setAttribute('src', 'data:image/png;base64,' . base64_encode($imgbinary));
                            $this->resNode->appendChild($imgNode);
                            // $imgStr.= '<img src="data:image/png;base64,'.base64_encode($imgbinary).'"/>';
                            // */
                        }
                        // $domNode->setAttribute('key', $this->resNode->getAttribute('path'));
                        $chunkNodes[] = $domNode;
                    } else {
                        $tooLong[] = $chunk['timestamp'];
                    }
                } else {
                    $tooFar[] = $chunk['timestamp'];
                }
            } else {
                $tooNot[] = $chunk['timestamp'];
            }
            // my_dump((memory_get_usage() >> 10) . ' kB');
        }
        fclose($this->fileHandle);
        // my_dump(get_execution_time());
        return;
        // my_dump((memory_get_usage() >> 10) . ' kB');
        // my_dump(count($chunkNodes). ' chunks read successfully!');
        foreach ($chunkNodes as $node) {
            $this->resNode->appendChild($node);
        }
        // my_dump(count($tooNot). ' chunks were too not there');
        // my_dump(count($tooFar). ' chunks were too far');
        // my_dump(count($tooLong). ' chunks were too long');
        
        // die();
        
        // $tagNode = \NBT\TAGNode::createDocument($this->getContent());
        // $domNode = \NBT\TAGNode::TAG2DOM($this->resDoc, $tagNode);
        // $domNode->setAttribute('key', $this->resNode->getAttribute('path'));
        // $this->resNode->appendChild($domNode);
    }

    protected function drawRow($row, $size)
    {
        $path = realpath(dirname(__FILE__) . '/../../mod/minecraft/res/blocks-' . $size . '.png');
        $pic = imagecreatefrompng($path);
        $xMax = count($row);
        $yMax = count(reset($row));
        $ret = imagecreatetruecolor($xMax * $size, $yMax * $size);
        imagefill($ret, 0, 0, imagecolorallocate($ret, 255, 255, 255));
        
        foreach ($row as $x => $col) {
            $x *= $size;
            foreach ($col as $y => $id) {
                if ($id) {
                    $y *= $size;
                    $id *= $size;
                    imagecopy($ret, $pic, $x, $y, $id, 0, 
                        // $size, $size,
                        $size, $size);
                }
            }
        }
        return $ret;
    }
}