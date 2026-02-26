<?php

declare(strict_types=1);

return [
    \MichielRoos\H5p\Domain\Model\Page::class => [
        'tableName' => 'pages',
    ],
    \MichielRoos\H5p\Domain\Model\FileReference::class => [
        'tableName' => 'sys_file_reference',
        'properties' => [
            'originalFileIdentifier' => [
                'fieldName' => 'uid_local',
            ],
        ],
    ],
];
