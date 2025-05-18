<?php
namespace MichielRoos\H5p\Domain\Repository;

use MichielRoos\H5p\Domain\Model\ContentTypeCacheEntry;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ContentTypeCacheEntryRepository extends Repository
{
    public function __construct(Typo3QuerySettings $querySettings = null)
    {
        parent::__construct();

        if ($querySettings !== null) {
            $querySettings->setRespectStoragePage(false);
            $this->setDefaultQuerySettings($querySettings);
        }
    }

    /**
     * Returns all cache entries as an array of stdObjects, the way the H5P core
     * expects it.
     *
     * @return array
     */
    public function getContentTypeCacheObjects(): array
    {
        $cacheEntries = [];
        foreach ($this->findAll() as $contentTypeCacheEntry) {
            /** @var ContentTypeCacheEntry $contentTypeCacheEntry */
            $cacheEntries[] = $contentTypeCacheEntry->toStdClass();
        }
        return $cacheEntries;
    }
}
