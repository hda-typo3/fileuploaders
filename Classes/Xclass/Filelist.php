<?php
namespace AEMKA\Fileuploaders\Xclass;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Filelist\Dto\ResourceView;
use TYPO3\CMS\Filelist\Type\Mode;

/**
 * Filelist
 */
class Filelist extends \TYPO3\CMS\Filelist\Filelist
{

    /**
     * Initialization of class
     *
     * @param Folder $folderObject The folder to work on
     * @param int $currentPage The current page to render
     * @param string $sort Sorting column
     * @param bool $sortRev Sorting direction
     * @param Mode $mode Mode of the file list
     */
    public function start(Folder $folderObject, int $currentPage, string $sort, bool $sortRev, Mode $mode = Mode::MANAGE)
    {
        parent::start($folderObject, $currentPage, $sort, $sortRev, $mode);
        $this->fieldArray[] = 'cruser_id';
    }

    /**
     * @param ResourceView[] $resourceViews
     */
    protected function renderListTableBody(array $resourceViews): string
    {
        $output = '';
        foreach ($resourceViews as $resourceView) {
            $data = [];
            $attributes = [
                'class' => $resourceView->isSelected ? 'selected' : '',
                'data-filelist-element' => 'true',
                'data-filelist-type' => $resourceView->getType(),
                'data-filelist-identifier' => $resourceView->getIdentifier(),
                'data-filelist-state-identifier' => $resourceView->getStateIdentifier(),
                'data-filelist-name' => htmlspecialchars($resourceView->getName()),
                'data-filelist-thumbnail' => $resourceView->getThumbnailUri(),
                'data-filelist-uid' => $resourceView->getUid(),
                'data-filelist-meta-uid' => $resourceView->getMetaDataUid(),
                'data-filelist-selectable' => $resourceView->isSelectable ? 'true' : 'false',
                'data-filelist-selected' => $resourceView->isSelected ? 'true' : 'false',
                'data-multi-record-selection-element' => 'true',
                'draggable' => $resourceView->canMove() ? 'true' : 'false',
            ];
            if ($this->getBackendUser()->checkLanguageAccess(0)) {
                $attributes['data-default-language-access'] = 'true';
            }
            foreach ($this->fieldArray as $field) {
                switch ($field) {
                    // aemka
                    case 'cruser_id':
                        $data[$field] = '';
                        if ($resourceView->resource instanceof File) {
                            $queryString = '"identifier":"' . $resourceView->getName() . '"';
                            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('sys_log');
                            $result = $queryBuilder
                                ->select('be_users.username')
                                ->from('sys_log')
                                ->leftJoin('sys_log', 'be_users', 'be_users', 'sys_log.userid = be_users.uid')
                                ->where(
                                    $queryBuilder->expr()->and(
                                        $queryBuilder->expr()->eq('sys_log.channel', $queryBuilder->createNamedParameter('file')),
                                        $queryBuilder->expr()->like('sys_log.log_data', $queryBuilder->createNamedParameter(
                                            '%' . $queryBuilder->escapeLikeWildcards($queryString) . '%'
                                        ))
                                    )
                                )
                                ->execute()
                                ->fetch();
                            $data[$field] = (isset($result['username'])) ? $result['username'] : '';
                        }
                        break;
                    // aemka
                    case 'icon':
                        $data[$field] = $this->renderIcon($resourceView);
                        break;
                    case 'name':
                        $data[$field] = $this->renderName($resourceView)
                            . $this->renderThumbnail($resourceView);
                        break;
                    case 'size':
                        $data[$field] = $this->renderSize($resourceView);
                        break;
                    case 'rw':
                        $data[$field] = $this->renderPermission($resourceView);
                        break;
                    case 'record_type':
                        $data[$field] = $this->renderType($resourceView);
                        break;
                    case 'crdate':
                        $data[$field] = $this->renderCreationTime($resourceView);
                        break;
                    case 'tstamp':
                        $data[$field] = $this->renderModificationTime($resourceView);
                        break;
                    case '_SELECTOR_':
                        $data[$field] = $this->renderSelector($resourceView);
                        break;
                    case '_PATH_':
                        $data[$field] = $this->renderPath($resourceView);
                        break;
                    case '_REF_':
                        $data[$field] = $this->renderReferenceCount($resourceView);
                        break;
                    case '_CONTROL_':
                        $data[$field] = $this->renderControl($resourceView);
                        break;
                    default:
                        $data[$field] = $this->renderField($resourceView, $field);
                }
            }
            $output .= $this->addElement($data, $attributes);
        }

        return $output;
    }

}
