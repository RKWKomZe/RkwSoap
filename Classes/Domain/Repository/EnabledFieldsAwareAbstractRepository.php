<?php
namespace RKW\RkwSoap\Domain\Repository;

use Madj2k\CoreExtended\Domain\Repository\StoragePidAwareAbstractRepository;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
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

}
