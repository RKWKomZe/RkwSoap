<?php
namespace RKW\RkwSoap\Domain\Repository;

use Madj2k\CoreExtended\Domain\Repository\StoragePidAwareAbstractRepository;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
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
 * Class EnabledFieldsAwareAbstractRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class EnabledFieldsAwareAbstractRepository extends StoragePidAwareAbstractRepository
{

    /**
     * initializeObject
     *
     * @return void
     * @throws InvalidConfigurationTypeException
     */
    public function initializeObject(): void
    {
        parent::initializeObject();

        $this->defaultQuerySettings->setIgnoreEnableFields(true);
        $this->defaultQuerySettings->setIncludeDeleted(true);
    }


    /**
     * Find all events that have been updated recently
     *
     * @param int $timestamp
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByTimestamp(int $timestamp): QueryResultInterface
    {
        $query = $this->createQuery();

        $query->matching(
            $query->greaterThanOrEqual('tstamp', $timestamp)
        );
        $query->setOrderings(array('tstamp' => QueryInterface::ORDER_ASCENDING));

        return $query->execute();
    }


    /**
     * Finds an object matching the given identifier.
     *
     * Alias of "findByIdentifier"
     *
     * toDo: The parent findByUid-function prevents typecast
     *
     * @param int $uid The identifier of the object to find
     * @return object|null
     */
    public function findByUid($uid):? object
    {
        return $this->findByIdentifier($uid);
    }


    /**
     * Finds an object matching the given identifier.
     *
     * Hint: Override needed to make the Repository default query settings usable
     *
     * toDo: The parent findByIdentifier-function prevents typecast
     *
     * @param int $identifier The identifier of the object to find
     * @return object|null
     */
    public function findByIdentifier($identifier):? object
    {
        $query = $this->createQuery();

        $query->matching(
            $query->equals('uid', $identifier)
        );

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }

}
