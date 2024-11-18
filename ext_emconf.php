<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "fileuploaders".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'File uploader usernames',
    'description' => 'Displays file uploader in file list module',
    'category' => 'backend',
    'version' => '12.0.0',
    'state' => 'alpha',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Matthias Krappitz',
    'author_email' => 'matthias@aemka.de',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

