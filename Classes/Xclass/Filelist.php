<?php
namespace AEMKA\Fileuploaders\Xclass;

use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ProcessedFile;

/**
 * Filelist
 */
class Filelist extends \TYPO3\CMS\Filelist\Filelist
{

    /**
     * Initialization of class
     *
     * @param Folder $folderObject The folder to work on
     * @param int $pointer Pointer
     * @param string $sort Sorting column
     * @param bool $sortRev Sorting direction
     */
    public function start(Folder $folderObject, $pointer, $sort, $sortRev)
    {
        parent::start($folderObject, $pointer, $sort, $sortRev);
        $this->fieldArray[] = 'cruser_id';
    }
    
    /**
     * This returns tablerows for the files in the array $items['sorting'].
     *
     * @param File[] $files File items
     * @return string HTML table rows.
     */
    public function formatFileList(array $files)
    {
        $out = '';
        foreach ($files as $fileObject) {
            // Initialization
            $this->counter++;
            $this->totalbytes += $fileObject->getSize();
            $ext = $fileObject->getExtension();
            $fileUid = $fileObject->getUid();
            $fileName = trim($fileObject->getName());
            // Preparing and getting the data-array
            $theData = [];
            // Preparing table row attributes
            $attributes = [
                'data-type' => 'file',
                'data-file-uid' => $fileUid,
            ];
            if ($this->isEditMetadataAllowed($fileObject)
                && ($metaDataUid = $fileObject->getMetaData()->offsetGet('uid'))
                ) {
                    $attributes['data-metadata-uid'] = (string)$metaDataUid;
                }
                foreach ($this->fieldArray as $field) {
                    switch ($field) {
                        // aemka
                        case 'cruser_id':
                            $theData[$field] = '';
                            if ($fileObject->getProperty($field)) {
                                $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('be_users');
                                $result = $queryBuilder
                                    ->select('username')
                                    ->from('be_users')
                                    ->where(
                                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($fileObject->getProperty($field), \PDO::PARAM_INT))
                                    )
                                    ->execute()
                                    ->fetch();
                                $theData[$field] = $result['username'];
                            }
                            break;
                        // aemka
                        case 'size':
                            $theData[$field] = GeneralUtility::formatSize((int)$fileObject->getSize(), htmlspecialchars($this->getLanguageService()->getLL('byteSizeUnits')));
                            break;
                        case 'rw':
                            $theData[$field] = '' . (!$fileObject->checkActionPermission('read') ? ' ' : '<strong class="text-danger">' . htmlspecialchars($this->getLanguageService()->getLL('read')) . '</strong>') . (!$fileObject->checkActionPermission('write') ? '' : '<strong class="text-danger">' . htmlspecialchars($this->getLanguageService()->getLL('write')) . '</strong>');
                            break;
                        case 'record_type':
                            $theData[$field] = htmlspecialchars($this->getLanguageService()->getLL('file') . ($ext ? ' (' . strtoupper($ext) . ')' : ''));
                            break;
                        case '_CONTROL_':
                            $theData[$field] = $this->makeEdit($fileObject);
                            break;
                        case '_SELECTOR_':
                            $theData[$field] = $this->makeCheckbox($fileObject);
                            break;
                        case '_REF_':
                            $theData[$field] = $this->makeRef($fileObject);
                            break;
                        case '_PATH_':
                            $theData[$field] = $this->makePath($fileObject);
                            break;
                        case 'icon':
                            $theData[$field] = (string)BackendUtility::wrapClickMenuOnIcon($this->getFileOrFolderIcon($fileName, $fileObject), 'sys_file', $fileObject->getCombinedIdentifier());
                            break;
                        case 'name':
                            // Edit metadata of file
                            $theData[$field] = $this->linkWrapFile(htmlspecialchars($fileName), $fileObject);
                            
                            if ($fileObject->isMissing()) {
                                $theData[$field] .= '<span class="label label-danger label-space-left">'
                                    . htmlspecialchars($this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:warning.file_missing'))
                                    . '</span>';
                                    // Thumbnails?
                            } elseif ($this->thumbs && ($fileObject->isImage() || $fileObject->isMediaFile())) {
                                $processedFile = $fileObject->process(
                                    ProcessedFile::CONTEXT_IMAGEPREVIEW,
                                    [
                                        'width' => (int)($this->getBackendUser()->getTSConfig()['options.']['file_list.']['thumbnail.']['width'] ?? 64),
                                        'height' => (int)($this->getBackendUser()->getTSConfig()['options.']['file_list.']['thumbnail.']['height'] ?? 64),
                                    ]
                                    );
                                $theData[$field] .= '<br /><img src="' . htmlspecialchars($processedFile->getPublicUrl() ?? '') . '" ' .
                                    'width="' . htmlspecialchars($processedFile->getProperty('width')) . '" ' .
                                    'height="' . htmlspecialchars($processedFile->getProperty('height')) . '" ' .
                                    'title="' . htmlspecialchars($fileName) . '" alt="" />';
                            }
                            break;
                        case 'crdate':
                            $crdate = $fileObject->getCreationTime();
                            $theData[$field] = $crdate ? BackendUtility::datetime($crdate) : '-';
                            break;
                        case 'tstamp':
                            $tstamp = $fileObject->getModificationTime();
                            $theData[$field] = $tstamp ? BackendUtility::datetime($tstamp) : '-';
                            break;
                        default:
                            $theData[$field] = '';
                            if ($fileObject->hasProperty($field)) {
                                $concreteTableName = $this->getConcreteTableName($field);
                                if ($field === ($GLOBALS['TCA'][$concreteTableName]['ctrl']['cruser_id'] ?? '')) {
                                    // Handle cruser_id by adding the avatar along with the username
                                    $theData[$field] = $this->getBackendUserInformation((int)$fileObject->getProperty($field));
                                } elseif ($field === 'storage') {
                                    // Fetch storage name of the current file
                                    $storage = GeneralUtility::makeInstance(StorageRepository::class)->findByUid((int)$fileObject->getProperty($field));
                                    if ($storage !== null) {
                                        $theData[$field] = htmlspecialchars($storage->getName());
                                    }
                                } else {
                                    $theData[$field] = htmlspecialchars(
                                        (string)BackendUtility::getProcessedValueExtra(
                                            $this->getConcreteTableName($field),
                                            $field,
                                            $fileObject->getProperty($field),
                                            $this->fixedL,
                                            $fileObject->getMetaData()->offsetGet('uid')
                                        )
                                    );
                                }
                            }
                    }
                }
                $out .= $this->addElement($theData, $attributes);
        }
        return $out;
    }

}
