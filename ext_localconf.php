<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function()
    {
        
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Filelist\FileList::class] = [
            'className' => AEMKA\Fileuploaders\Xclass\Filelist::class
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:filelist/Resources/Private/Language/locallang_mod_file_list.xlf'][] = 'EXT:fileuploaders/Resources/Private/Language/locallang_mod_file_list.xlf';

    }
);