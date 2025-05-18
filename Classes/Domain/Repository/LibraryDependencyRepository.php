<?php
namespace MichielRoos\H5p\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

class LibraryDependencyRepository extends Repository
{
    public function __construct(Typo3QuerySettings $querySettings = null)
    {
        parent::__construct();

        if ($querySettings !== null) {
            $querySettings->setRespectStoragePage(false);
            $this->setDefaultQuerySettings($querySettings);
        }
    }

    public function findOneByLibraryAndRequiredLibrary(string $library, string $requiredLibrary)
    {
        $query = $this->createQuery();
        $dependencies = $query->matching(
            $query->logicalAnd(
                $query->equals('library', $library),
                $query->equals('required_library', $requiredLibrary)
            )
        )->execute();

        return $dependencies->count() ? $dependencies->getFirst() : null;
    }
}
