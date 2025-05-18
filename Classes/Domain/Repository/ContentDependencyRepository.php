<?php

namespace MichielRoos\H5p\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ContentDependencyRepository extends Repository
{
    protected $defaultOrderings = [
        'weight' => QueryInterface::ORDER_ASCENDING,
    ];

    public function __construct(Typo3QuerySettings $querySettings = null)
    {
        parent::__construct();

        if ($querySettings !== null) {
            $querySettings->setRespectStoragePage(false);
            $this->setDefaultQuerySettings($querySettings);
        }
    }

    public function findByContentAndType($content, $type)
    {
        $query = $this->createQuery();
        return $query->matching(
            $query->logicalAnd(
                $query->equals('content', $content),
                $query->equals('dependency_type', $type)
            )
        )->execute();
    }
}
