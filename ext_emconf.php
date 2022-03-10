<?php

$EM_CONF['imgproxy'] = [
    'title' => 'ImgProxy',
    'description' => 'Create and serve images with imgproxy.',
    'category' => 'fe',
    'state' => 'alpha',
    'author' => 'Christoph Lehmann',
    'author_email' => 'post@christophlehmann.eu',
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.5.0-11.5.99',
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
