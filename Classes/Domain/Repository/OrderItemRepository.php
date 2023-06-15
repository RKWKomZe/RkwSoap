<?php

namespace RKW\RkwSoap\Domain\Repository;

use RKW\RkwSoap\Domain\Model\FrontendUser;
use RKW\RkwSoap\Domain\Model\OrderItem;
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
 * Class OrderItemRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OrderItemRepository extends EnabledFieldsAwareAbstractRepository
{
    /**
     * Find all order items by order uid
     *
     * @api used by RKW Soap
     * @param int $orderUid
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByOrderUid(int $orderUid): QueryResultInterface
    {

        $query = $this->createQuery();

        $query->matching(
            $query->equals('order', $orderUid)
        );

        return $query->execute();
    }

}
