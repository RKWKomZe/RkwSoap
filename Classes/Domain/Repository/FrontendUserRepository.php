<?php

namespace RKW\RkwSoap\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

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

        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

        // don't add the pid constraint
        $querySettings->setRespectStoragePage(false);
        $querySettings->setIgnoreEnableFields(true);
        $querySettings->setIncludeDeleted(true);

        $this->setDefaultQuerySettings($querySettings);

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
