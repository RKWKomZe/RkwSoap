<?php

namespace RKW\RkwSoap\Domain\Repository;

use RKW\RkwSoap\Domain\Model\FrontendUser;
use RKW\RkwSoap\Domain\Model\Product;
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
 * Class ProductRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductRepository extends EnabledFieldsAwareAbstractRepository
{
    /**
     * toDo: This override is only a workaround, because the "initializedObject"-configuration does not work with the core repository
     *
     * @param mixed $identifier The identifier of the object to find
     * @return Product|null The matching object if found, otherwise NULL
     */
    public function findByIdentifier($identifier): ?Product
    {
        $query = $this->createQuery();

        $query->matching(
            $query->equals('uid', $identifier)
        );

        /** @var Product $returnValue */
        $returnValue = $query->execute()->getFirst();

        return $returnValue;
    }


    /**
     * Get all products including hidden and deleted
     *
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAll()
    {
        $query = $this->createQuery();

        return $query->execute();
    }


    /**
     * Finds an object matching the given identifier.
     *
     * toDo: The parent findByUid-function prevents typecast
     *
     * @param int $uid The identifier of the object to find
     * @return Product|null The matching object if found, otherwise NULL
     */
    public function findByUid($uid):? Product
    {
        $query = $this->createQuery();

        $query->matching(
            $query->equals('uid', $uid)
        );

        $query->setLimit(1);

        /** @var Product $returnValue */
        $returnValue = $query->execute()->getFirst();
        return $returnValue;
    }
}
