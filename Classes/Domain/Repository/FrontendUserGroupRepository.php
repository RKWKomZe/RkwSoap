<?php
namespace RKW\RkwSoap\Domain\Repository;

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

use Madj2k\CoreExtended\Domain\Repository\StoragePidAwareAbstractRepository;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class FrontendUserGroupRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserGroupRepository extends EnabledFieldsAwareAbstractRepository
{

    /**
     * initializeObject
     *
     * @return void
     * @throws InvalidConfigurationTypeException

    public function initializeObject(): void
    {
        parent::initializeObject();

        $querySettings = $this->objectManager->get(Typo3QuerySettings::class);

        // don't add the pid constraint
        $querySettings->setIgnoreEnableFields(true);
        $querySettings->setIncludeDeleted(true);

        $this->setDefaultQuerySettings($querySettings);
    }*/


    /**
     * Find all users that have been updated recently
     *
     * @param int $timestamp
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByTimestamp(int $timestamp)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->greaterThanOrEqual('tstamp', $timestamp)
        );

        $query->setOrderings(array('tstamp' => QueryInterface::ORDER_ASCENDING));

        return $query->execute();
    }

}
