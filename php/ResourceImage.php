<?php
/***********************************************************************
 * Slothsoft\Farah\ResourceImage v1.00 19.10.2012 © Daniel Schulz
 * 
 * 	Changelog:
 *		v1.00 19.10.2012
 *			initial release
 ***********************************************************************/
namespace Slothsoft\Farah;

use Slothsoft\Core\Image;
use Exception;

class ResourceImage extends Resource
{

    protected $resFile = null;

    public function asDocument()
    {
        return $this->resFile ? $this->resFile : parent::asDocument();
    }

    protected function loadFileStatus()
    {
        parent::loadFileStatus();
        try {
            if ($info = getimagesize($this->getPath())) {
                $this->resNode->setAttribute('width', $info[0]);
                $this->resNode->setAttribute('height', $info[1]);
                $this->resNode->setAttribute('bits', $info['bits']);
            }
            $this->resNode->setAttribute('thumbnail', $this->getUri() . '?load=thumbnail');
            /*
             * if ($tn = Image::generateThumbnail($this->getPath())) {
             * $this->resNode->setAttribute('thumbnail', $tn);
             * }
             * //
             */
        } catch (Exception $e) {}
    }

    protected function loadFileThumbnail()
    {
        if ($tn = Image::generateThumbnail($this->getPath(), null, null, false)) {
            $this->resFile = HTTPFile::createFromPath($tn);
        }
    }
}