<?php

namespace Lemming\ImgProxy\EventListener;

use Lemming\ImgProxy\Processor\ImgProxyProcessor;
use TYPO3\CMS\Core\Resource\Event\BeforeFileProcessingEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BeforeFileProcessing
{
    public function __invoke(BeforeFileProcessingEvent $event): void
    {
        $processor = GeneralUtility::makeInstance(ImgProxyProcessor::class);
        $task = $event->getProcessedFile()->getTask();
        if ($processor->canProcessTask($task)) {
            $processor->processTask($task);
            $task->setExecuted(true);
        }
    }
}
