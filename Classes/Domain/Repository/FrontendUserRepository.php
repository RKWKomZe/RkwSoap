<?php

namespace RKW\RkwSoap\Domain\Repository;

use RKW\RkwSoap\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepository extends EnabledFieldsAwareAbstractRepository
{

    /**
     * toDo: This override is only a workaround, because the "initializedObject"-configuration does not work with the core repository
     *
     * @param mixed $identifier The identifier of the object to find
     * @return FrontendUser|null The matching object if found, otherwise NULL
     */
    public function findByIdentifier($identifier): ?FrontendUser
    {
        $query = $this->createQuery();

        $query->matching(
            $query->equals('uid', $identifier)
        );

        /** @var FrontendUser $returnValue */
        $returnValue = $query->execute()->getFirst();

        return $returnValue;
    }


    /**
     * Find all users that have been updated recently
     *
     * @param int  $timestamp
     * @param bool $excludeEmptyName
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByTimestamp(int $timestamp, bool $excludeEmptyName = true): QueryResultInterface
    {
        $query = $this->createQuery();
        $constrains = array(
            $query->greaterThanOrEqual('tstamp', $timestamp),
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
    }

}
