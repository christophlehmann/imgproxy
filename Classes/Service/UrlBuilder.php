<?php

namespace Lemming\ImgProxy\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UrlBuilder
{
    protected $imgProxyUrl;

    protected $key;

    protected $salt;

    protected $sourceUrl;

    protected $options = [];

    public function __construct(string $imgProxyUrl, $key, $salt)
    {
        $this->imgProxyUrl = $imgProxyUrl;
        $this->key = pack("H*" , $key);
        $this->salt = pack("H*" , $salt);
    }

    public function setSourceUrl(string $sourceUrl)
    {
        $this->sourceUrl = $sourceUrl;
        return $this;
    }

    public function setWidth(int $width) {
        $this->options[] = 'w:' . $width;
        return $this;
    }

    public function setHeight(int $height)
    {
        $this->options[] = 'h:' . $height;
        return $this;
    }

    public function setCrop(float $width, float $height, string $cropGravityType, float $offsetX, float $offsetY)
    {
        $this->options[] = implode(':', ['c', $width, $height, $cropGravityType, $offsetX, $offsetY]);
        return $this;
    }

    public function setFilename(string $filename)
    {
        $this->options[] = 'fn:' . $filename;
        return $this;
    }

    public function setQuality(int $quality)
    {
        $this->options[] = 'q:' . $quality;
        return $this;
    }

    public function setFormatQuality(string $formatQuality)
    {
        $this->options[] = 'fq:' . $formatQuality;
        return $this;
    }

    public function setCacheBuster(string $cacheBuster)
    {
        $this->options[] = 'cb:' . $cacheBuster;
        return $this;
    }

    public function allowUpscaling()
    {
        $this->options[] = 'el:1';
    }

    public function generate(): string
    {
        $this->options[] = rtrim(strtr(base64_encode($this->sourceUrl), '+/', '-_'), '=');
        $unsignedPath = '/' . implode('/', $this->options);
        $sha256 = hash_hmac('sha256', $this->salt . $unsignedPath, $this->key, true);
        $sha256Encoded = base64_encode($sha256);
        $signature = str_replace(["+", "/", "="], ["-", "_", ""], $sha256Encoded);

        $baseUrl = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('imgproxy', 'imgproxyUrl');

        return rtrim($baseUrl, '/') . '/' . $signature . $unsignedPath;
    }
}