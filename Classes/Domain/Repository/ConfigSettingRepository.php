<?php
namespace MichielRoos\H5p\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class ConfigSettingRepository
 */
class ConfigSettingRepository extends Repository
{
    public function __construct(
        Typo3QuerySettings $querySettings
    ) {
        parent::__construct();

        // Disable storage page restriction
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }
}
