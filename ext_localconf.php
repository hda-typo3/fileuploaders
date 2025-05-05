<?php
declare(strict_types=1);

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Filelist\FileList::class] = [
    'className' => AEMKA\Fileuploaders\Xclass\Filelist::class
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:filelist/Resources/Private/Language/locallang_mod_file_list.xlf'][] = 'EXT:fileuploaders/Resources/Private/Language/locallang_mod_file_list.xlf';
