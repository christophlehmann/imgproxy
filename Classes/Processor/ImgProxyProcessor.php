<?php

namespace Lemming\ImgProxy\Processor;

use Lemming\ImgProxy\Service\UrlBuilder;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Imaging\ImageDimension;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Processing\ProcessorInterface;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImgProxyProcessor implements ProcessorInterface
{
    protected $configuration = [];

    public function __construct(
        ExtensionConfiguration $extensionConfiguration = null
    ) {
        $this->configuration = $extensionConfiguration ? $extensionConfiguration->get('imgproxy') :
            GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('imgproxy');
    }

    public function canProcessTask(TaskInterface $task)
    {
        $allowedFileExtensions = GeneralUtility::trimExplode(',',
            empty($this->configuration['allowedExtensions']) ? 'jpg,jpeg,webp,avif,png,gif,tiff' : $this->configuration['allowedExtensions']
        );
        return (
            !empty($this->configuration['imgproxyUrl'])
            && $task->getSourceFile()->getStorage()->isPublic()
            && in_array(strtolower($task->getSourceFile()->getExtension()), $allowedFileExtensions)
            && in_array($task->getName(), ['Preview', 'CropScaleMask'], true)
            && $task->getSourceFile()->getProperty('width') > 0
            && $task->getSourceFile()->getProperty('height') > 0
        );
    }

    public function processTask(TaskInterface $task)
    {
        $processedFile = $task->getTargetFile();
        $processingConfiguration = $processedFile->getProcessingConfiguration();
        $imageDimension = ImageDimension::fromProcessingTask($task);
        $sourceFileWidth = $task->getSourceFile()->getProperty('width');
        $sourceFileHeight = $task->getSourceFile()->getProperty('height');

        $urlBuilder = new UrlBuilder($this->configuration['imgproxyUrl'], $this->configuration['key'], $this->configuration['salt']);
        $urlBuilder->setWidth($imageDimension->getWidth())
            ->setHeight($imageDimension->getHeight())
            ->setSourceUrl($this->getPublicUrlOfSourceFile($task->getSourceFile()))
            ->setFilename(str_replace('.' . $task->getTargetFileExtension(), '', $task->getTargetFileName()))
            ->setCacheBuster($task->getSourceFile()->getSha1());

        if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['processor_allowUpscaling']) {
            $urlBuilder->allowUpscaling();
        }

        if (!empty($this->configuration['formatQuality'])) {
            $urlBuilder->setFormatQuality($this->configuration['formatQuality']);
        } else {
            $urlBuilder->setQuality($GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality']);
        }

        $cropData = $processingConfiguration['crop'] ?? false;
        if (is_string($cropData) && $cropArea = json_decode($cropData)) {
            $urlBuilder->setCrop(
                $cropArea->width,
                $cropArea->height,
                'fp',
                ($cropArea->x + ($cropArea->width / 2)) / $sourceFileWidth,
                ($cropArea->y + ($cropArea->height / 2)) / $sourceFileHeight,
            );
            $urlBuilder->setResizeType('force');
        } elseif ($cropData instanceof Area) {
            $urlBuilder->setCrop(
                $cropData->getWidth(),
                $cropData->getHeight(),
                'fp',
                ($cropData->getOffsetLeft() + ($cropData->getWidth() / 2)) / $sourceFileWidth,
                ($cropData->getOffsetTop() + ($cropData->getHeight() / 2)) / $sourceFileHeight,
            );
            $urlBuilder->setResizeType('force');
        } elseif (
            str_ends_with($processingConfiguration['width'], 'c') ||
            str_ends_with($processingConfiguration['height'], 'c')
        ) {
            $urlBuilder->setResizeType('fill');
        }

        $processedFile->setName($task->getTargetFileName());
        $processedFile->updateProperties(
            [
                'width' => $imageDimension->getWidth(),
                'height' => $imageDimension->getHeight(),
                'size' => 0,
                'checksum' => $task->getConfigurationChecksum(),
                'processing_url' => $urlBuilder->generate()
            ]
        );

        $task->setExecuted(true);
    }

    protected function getPublicUrlOfSourceFile(FileInterface $sourceFile): string
    {
        $publicUrl = $sourceFile->getPublicUrl();
        if (!str_starts_with($publicUrl, 'http://') && !str_starts_with($publicUrl, 'https://')) {
            if (!empty($this->configuration['helperUrl'])) {
                $publicUrl = rtrim($this->configuration['helperUrl'], '/') . '/' . ltrim($publicUrl, '/');
            } else {
                $publicUrl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/' . ltrim($publicUrl, '/');
            }
        }

        return $publicUrl;
    }
}