<?php
namespace MichielRoos\H5p\Domain\Repository;

use MichielRoos\H5p\Domain\Model\Library;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class LibraryRepository extends Repository
{
    protected $defaultOrderings = [
        'title'        => QueryInterface::ORDER_ASCENDING,
        'majorVersion' => QueryInterface::ORDER_ASCENDING,
        'minorVersion' => QueryInterface::ORDER_ASCENDING,
        'patchVersion' => QueryInterface::ORDER_ASCENDING,
    ];

    public function __construct(Typo3QuerySettings $querySettings = null)
    {
        parent::__construct();

        if ($querySettings !== null) {
            $querySettings->setRespectStoragePage(false);
            $this->setDefaultQuerySettings($querySettings);
        }
    }

    public function removeByLibraryId($id): void
    {
        $library = $this->findOneByUid($id);
        if ($library !== null) {
            $this->remove($library);
        }
    }

    public function findLibrariesWithNewerVersion(Library $library)
    {
        $query = $this->createQuery();
        $query->getQueryBuilder()
            ->where('e.name = ?0 AND e.libraryId != ?1 AND (e.majorVersion > ?2 OR (e.majorVersion = ?2 AND e.minorVersion > ?3))')
            ->setParameters([
                $library->getTitle(),
                $library->getUid(),
                $library->getMajorVersion(),
                $library->getMinorVersion(),
            ]);

        return $query->execute();
    }

    public function getPatchedLibrary($name, $majorVersion, $minorVersion, $patchVersion)
    {
        $query = $this->createQuery();
        $query->getQueryBuilder()
            ->where('e.name = ?0 AND e.majorVersion = ?1 AND e.minorVersion = ?2 AND e.patchVersion > ?3')
            ->setParameters([
                $name,
                $majorVersion,
                $minorVersion,
                $patchVersion,
            ]);

        return $query->execute();
    }

    public function findUnused(): array
    {
        $libs = $this->findAll()->toArray();
        return array_filter($libs, static function ($library) {
            /** @var Library $library */
            return $library->getContents()->count() === 0 &&
                $library->getContentDependencies()->count() === 0 &&
                count($library->getDependentLibraries()) === 0;
        });
    }

    public function findOneByMachinenameMajorVersionAndMinorVersion($libraryName, $majorVersion, $minorVersion = 0)
    {
        $query = $this->createQuery();
        $libraries = $query->matching(
            $query->logicalAnd(
                $query->equals('machine_name', $libraryName),
                $query->equals('major_version', $majorVersion),
                $query->equals('minor_version', $minorVersion)
            )
        )->execute();

        return $libraries->count() ? $libraries->getFirst() : null;
    }

    public function findOneByNameMajorVersionAndMinorVersion($libraryName, $majorVersion, $minorVersion = 0)
    {
        $query = $this->createQuery();
        $libraries = $query->matching(
            $query->logicalAnd(
                $query->equals('title', $libraryName),
                $query->equals('major_version', $majorVersion),
                $query->equals('minor_version', $minorVersion)
            )
        )->execute();

        return $libraries->count() ? $libraries->getFirst() : null;
    }

    public function getLibraryAddons(): array
    {
        $query = $this->createQuery();
        $sql = <<<EOS
SELECT e.uid,
       e.title         AS machineName,
       e.major_version AS majorVersion,
       e.minor_version AS minorVersion,
       e.patch_version AS patchVersion,
       e.add_to        AS addTo,
       e.preloaded_js  AS preloadedJs,
       e.preloaded_css AS preloadedCSS
FROM tx_h5p_domain_model_library e
         LEFT JOIN tx_h5p_domain_model_library l2
         ON e.title = l2.title
            AND (
                e.major_version < l2.major_version OR
                (e.major_version = l2.major_version AND e.minor_version < l2.minor_version)
            )
WHERE e.add_to IS NOT NULL
  AND l2.title IS NULL
EOS;

        return $query->statement($sql)->execute(true);
    }
}
