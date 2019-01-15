<?php

namespace Kollarovic\Thumbnail;
use Exception;
use Nette;


/**
* @author  Mario Kollarovic
*
* Generator
*/
class Generator extends AbstractGenerator
{

	/**
	 * @return void
 	 */
	protected function createThumb()
	{
		try {

			$image = Nette\Utils\Image::fromFile($this->src);
            if ($image->width > $this->width) {
                $image->resize($this->width, $this->height, $this->crop ? Nette\Utils\Image::EXACT : Nette\Utils\Image::FIT);
                $image->sharpen();
                try {
                    if ($this->disableWebp) {
                        $image->save($this->desc);
                    }else{
                        imagewebp($image->getImageResource(), $this->desc);
                    }
                } catch (Exception $e) {
                }
            }
        } catch (Nette\Utils\UnknownImageFileException $e) {
		}
	}

}
