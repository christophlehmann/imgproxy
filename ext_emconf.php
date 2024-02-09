<?php

$EM_CONF['imgproxy'] = [
    'title' => 'ImgProxy',
    'description' => 'Create and serve images with imgproxy.',
    'category' => 'fe',
    'state' => 'alpha',
    'author' => 'Christoph Lehmann',
    'author_email' => 'post@christophlehmann.eu',
    'version' => '0.0.3',
    'constraints' => [
        'depends' => [
            'typo3' => '10.5.0-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'classmap' => ['Classes'],
    ]
];
