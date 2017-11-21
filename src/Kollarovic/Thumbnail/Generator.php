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
		$image = Nette\Image::fromFile($this->src);
		$image->resize($this->width, $this->height, $this->crop ? Nette\Image::EXACT : Nette\Image::FIT);
		try{
			$image->save($this->desc);
		}catch(Exception $e){
			
		}
	}

}
