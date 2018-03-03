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
			$image->resize($this->width, $this->height, $this->crop ? Nette\Utils\Image::EXACT : Nette\Utils\Image::FIT);
			$image->sharpen();
			try {
				$image->save($this->desc);
			} catch (Exception $e) {

			}
		} catch (Nette\Utils\UnknownImageFileException $e) {
		}
	}

}
