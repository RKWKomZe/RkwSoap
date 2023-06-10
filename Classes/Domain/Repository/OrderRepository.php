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

}
