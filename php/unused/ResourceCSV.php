<?php
/***********************************************************************
 * Slothsoft\Farah\ResourceCSV v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

use Exception;

class ResourceCSV extends Resource
{

    protected function loadFileXML()
    {
        $del = $this->options['del'];
        $sep = $this->options['sep'];
        if ($sep === '') {
            $sep = chr(8);
        }
        $esc = $this->options['esc'];
        if ($esc === '') {
            // https://bugs.php.net/bug.php?id=51496
            $esc = chr(8);
        }
        $minLength = (int) $this->options['min-length'];
        $cols = (int) $this->options['cols'];
        $keyCol = (int) $this->options['key-col'];
        $valCol = (int) $this->options['val-col'];
        $rowList = [];
        $this->openHandle();
        while (($row = fgetcsv($this->fileHandle, 0, $del, $sep, $esc)) !== false) {
            if ($cols === 0 or count($row) === $cols) {
                $rowList[] = $row;
            }
        }
        $this->closeHandle();
        
        $headRow = null;
        switch ($this->options['output']) {
            case 'thead':
                $headRow = array_shift($rowList);
                break;
        }
        
        foreach ($rowList as $row) {
            $node = $this->resDoc->createElement('line');
            switch ($this->options['output']) {
                case 'assoc':
                    if ($keyCol === - 1) {
                        $key = reset($row);
                    } else {
                        $key = $row[$keyCol];
                    }
                    if ($valCol === - 1) {
                        $val = end($row);
                    } else {
                        $val = $row[$valCol];
                    }
                    $node->setAttribute('key', trim($key));
                    $node->setAttribute('val', trim($val));
                    break;
                case 'table':
                    foreach ($row as $val) {
                        $val = trim($val);
                        if (strlen($val) >= $minLength) {
                            $child = $this->resDoc->createElement('cell');
                            $child->setAttribute('val', $val);
                            $node->appendChild($child);
                        }
                    }
                    break;
                case 'thead':
                    if ($headRow) {
                        foreach ($headRow as $i => $key) {
                            if (strlen($key) and isset($row[$i])) {
                                $val = $row[$i];
                                try {
                                    if (strlen($val) >= $minLength) {
                                        $key = preg_replace('/\s+/u', '', $key);
                                        $node->setAttribute($key, $val);
                                    }
                                } catch (Exception $e) {
                                    // my_dump($e->getMessage());
                                }
                            }
                        }
                    }
                    break;
            }
            $this->resNode->appendChild($node);
        }
    }

    protected function loadFileXML_old()
    {
        $del = preg_quote($this->options['del']);
        $sep = preg_quote($this->options['sep']);
        $cols = (int) $this->options['cols'];
        $pattern = array();
        if ($cols) {
            for ($i = 0; $i < $cols; $i ++) {
                $pattern[] = $sep . '(.*)' . $sep;
            }
        } else {
            trigger_error('CSV does not support unlimited rows yet!', E_USER_WARNING);
            // die($this->resNode->ownerDocument->saveXML($this->resNode));
        }
        $pattern = '/^' . implode($del, $pattern) . '$/';
        $arr = $this->getArray();
        foreach ($arr as $row) {
            if (preg_match($pattern, $row, $match)) {
                
                $node = $this->resDoc->createElement('line');
                switch ($this->options['output']) {
                    case 'assoc':
                        array_shift($match);
                        $node->setAttribute('key', trim(reset($match)));
                        $node->setAttribute('val', trim(end($match)));
                        break;
                    case 'table':
                        array_shift($match);
                        foreach ($match as $cell) {
                            $child = $this->resDoc->createElement('cell');
                            $child->setAttribute('val', trim($cell));
                            $node->appendChild($child);
                        }
                        break;
                }
                $this->resNode->appendChild($node);
            }
        }
    }
}