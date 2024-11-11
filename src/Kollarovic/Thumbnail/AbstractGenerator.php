<?php

namespace Kollarovic\Thumbnail;

use Nette;
use    Nette\Http\IRequest;


/**

 * @author  Mario Kollarovic
 *
 * AbstractGenerator
 */
abstract class AbstractGenerator
{

    /** @var string */
    protected $src;

    /** @var string */
    protected $originalSrc;

    /** @var string */
    protected $desc;

    /** @var int */
    protected $width;

    /** @var int */
    protected $height;

    /** @var bool */
    protected $crop;

    /** @var string */
    protected $wwwDir;

    /** @var IRequest */
    private $httpRequest;

    /** @var string */
    private $thumbPathMask;

    /** @var string */
    private $placeholder;

    /** @var bool */
    protected $disableWebp;

    /** @var bool */
    protected $useWebP;

    /**
     * @param string
     * @param IRequest
     * @param string
     * @param string
     */
    function __construct($wwwDir, IRequest $httpRequest, $thumbPathMask, $placeholder)
    {
        $this->wwwDir = $wwwDir;
        $this->httpRequest = $httpRequest;
        $this->thumbPathMask = $thumbPathMask;
        $this->placeholder = $placeholder;
        $this->useWebP = false;
        $this->wwwDir = str_replace("/..", "", $this->wwwDir);

        if (array_key_exists('HTTP_ACCEPT', $_SERVER)) {
            if (strpos($_SERVER["HTTP_ACCEPT"], 'image/webp') !== false) {
                $this->useWebP = true;
            }
        }
    }


    /**
     * @param string
     * @param int
     * @param int
     * @param bool
     * @return string
     */
    public function thumbnail($src, $width, $height = NULL, $crop = false, $disableWebp = false)
    {

        if (str_contains($src, "http://") || str_contains($src, "https://")) {
            $parsedUrl = parse_url($src);
            $path = $parsedUrl['path'];
            $filename = basename($path);
            $imageData = file_get_contents($src);
            if (!is_dir($this->wwwDir ."/files/downloads")){
                mkdir($this->wwwDir ."/files/downloads");
            }
            if (!file_exists($this->wwwDir ."/files/downloads/". $filename)) {
                file_put_contents($this->wwwDir . "/files/downloads/" . $filename, $imageData);
            }
            $src = "/files/downloads/". $filename;
        }

        $this->disableWebp = $disableWebp;
        $this->src = $this->wwwDir . '/' . $src;
        $this->originalSrc = $src;

        $this->width = $width;
        $this->height = $height;
        $this->crop = $crop;

        if (!is_file($this->src) && !str_contains($this->src, "https://")) {
            return $this->createPlaceholderPath();
        }

        $thumbRelPath = $this->createThumbPath();
        $this->desc = $this->wwwDir . '/' . $thumbRelPath;

        if (!file_exists($this->desc) or (filemtime($this->desc) < filemtime($this->src))) {
            $this->createDir();
            $this->createThumb();
            clearstatcache();
        }

        if (file_exists($this->wwwDir . '/' . $thumbRelPath)){
            return $this->httpRequest->getUrl()->basePath . $thumbRelPath;
        }
        return $src;

    }


    /**
     * @return void
     */
    abstract protected function createThumb();


    /**
     * @return void
     */
    private function createDir()
    {
        $dir = dirname($this->desc);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }


    /**
     * @return string
     */
    private function createThumbPath()
    {
        $pathinfo = pathinfo($this->src);
        $md5 = md5($this->src);
        $md5Dir = $md5[0] . "/" . $md5[1] . "/" . $md5[2] . "/" . $md5;
        $search = array('{width}', '{height}', '{crop}', '{filename}', '{extension}', "{md5}");

        if ($this->disableWebp || !$this->useWebP) {
            $replace = array($this->width, $this->height, (int)$this->crop, $pathinfo['filename'], $pathinfo['extension'], $md5);
        } else {
            $replace = array($this->width, $this->height, (int)$this->crop, $pathinfo['filename'], 'webp', $md5);
        }
        return str_replace($search, $replace, $this->thumbPathMask);

    }


    /**
     * @return string
     */
    private function createPlaceholderPath()
    {
        $width = $this->width === NULL ? $this->height : $this->width;
        $height = $this->height === NULL ? $this->width : $this->height;
        $search = array('{width}', '{height}', '{src}');
        $replace = array($width, $height, $this->src);
        return str_replace($search, $replace, $this->placeholder);
    }

    public function setWwwDir(string $wwwDir){
        $this->wwwDir = $wwwDir;
    }

}
