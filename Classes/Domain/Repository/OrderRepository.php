<?php

namespace RKW\RkwSoap\Domain\Repository;

use RKW\RkwSoap\Domain\Model\Order;
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
 * Class OrderRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderRepository extends EnabledFieldsAwareAbstractRepository
{
    /**
     * toDo: This override is only a workaround, because the "initializedObject"-configuration does not work with the core repository
     *
     * @param mixed $identifier The identifier of the object to find
     * @return Order|null The matching object if found, otherwise NULL
     */
    public function findByIdentifier($identifier): ?Order
    {
        $query = $this->createQuery();

        $query->matching(
            $query->equals('uid', $identifier)
        );

        /** @var Order $returnValue */
        $returnValue = $query->execute()->getFirst();

        return $returnValue;
    }


    /**
     * Find all orders that have been updated recently
     *
     * @param int $timestamp
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByTimestamp(int $timestamp): QueryResultInterface
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalOr(
                $query->greaterThanOrEqual('tstamp', $timestamp),
                $query->greaterThanOrEqual('orderItem.tstamp', $timestamp)
            )
        );

        $query->setOrderings(array('tstamp' => QueryInterface::ORDER_ASCENDING));
        return $query->execute();
    }


    /**
     * Finds an object matching the given identifier.
     *
     * toDo: The parent findByUid-function prevents typecast
     *
     * @param int $uid The identifier of the object to find
     * @return \RKW\RkwShop\Domain\Model\Order|null
     */
    public function findByUid($uid):? Order
    {
        $query = $this->createQuery();

        $query->matching(
            $query->equals('uid', $uid)
        );

        $query->setLimit(1);

        /** @var Order $returnValue */
        $returnValue = $query->execute()->getFirst();
        return $returnValue;
    }

}
