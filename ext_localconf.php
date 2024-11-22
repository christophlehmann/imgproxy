<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processors']['ImgProxyProcessor'] = [
    'className' => \Lemming\ImgProxy\Processor\ImgProxyProcessor::class,
    'before' => [
        // On top of all
        'SvgImageProcessor'
    ],
];