<?php

namespace RKW\RkwSoap\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class FrontendUserRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{


    /**
     * initializeObject
     *
     * @return void
     */
    public function initializeObject()
    {
        /** @var QuerySettingsInterface $querySettings */
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        // Show comments from all pages
        $this->defaultQuerySettings->setRespectStoragePage(false);
        $this->defaultQuerySettings->setIgnoreEnableFields(true);
        $this->defaultQuerySettings->setIncludeDeleted(true);
    }


    /**
     * toDo: This override is only a workaround, because the "initializedObject"-configuration does not work with "findByIdentifier"
     *
     * @param mixed $identifier The identifier of the object to find
     * @return object The matching object if found, otherwise NULL
     */
    public function findByIdentifier($identifier)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->equals('uid', $identifier)
        );

        return $query->execute()->getFirst();

    }


    /**
     * Find all users that have been updated recently
     *
     * @param int $timestamp
     * @param bool    $excludeEmptyName
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByTimestamp($timestamp, $excludeEmptyName = true)
    {
        $query = $this->createQuery();
        $constrains = array(
            $query->greaterThanOrEqual('tstamp', intval($timestamp)),
        );

        // exclude feUsers without first- and last-name
        if ($excludeEmptyName) {
            $constrains[] = $query->logicalAnd(
                $query->logicalNot(
                    $query->equals('firstName', '')
                ),
                $query->logicalNot(
                    $query->equals('lastName', '')
                )
            );
        }

        $query->matching(
            $query->logicalAnd(
                $constrains
            )
        );

        $query->setOrderings(array('tstamp' => QueryInterface::ORDER_ASCENDING));

        return $query->execute();
        //===
    }

}
