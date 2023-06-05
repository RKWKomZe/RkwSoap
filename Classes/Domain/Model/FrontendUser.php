<?php
namespace RKW\RkwSoap\Domain\Model;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class FrontendUser
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
if (ExtensionManagementUtility::isLoaded('fe_register')) {
    class FrontendUser extends \Madj2k\FeRegister\Domain\Model\FrontendUser
    {

    }
} else {
    class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
    {

        /**
         * @var int
         */
        protected int $crdate = 0;


        /**
         * @var int
         */
        protected int $tstamp = 0;


        /**
         * @var bool
         */
        protected bool $disable = false;


        /**
         * @var bool
         */
        protected bool $deleted = false;


        /**
         * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwSoap\Domain\Model\FrontendUserGroup>|null
         */
        protected $usergroup = null;


        /**
         * initialize objectStorage
         *
         */
        public function __construct()
        {
            parent::__construct();
            $this->usergroup = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        }


        /**
         * Returns the usergroups. Keep in mind that the property is called "usergroup"
         * although it can hold several usergroups.
         *
         * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage|null An object storage containing the usergroup
         * @api
         */
        public function getUsergroup(): ?ObjectStorage
        {
            return $this->usergroup;
        }


        /**
         * Returns the crdate value
         *
         * @return int
         * @api
         */
        public function getCrdate(): int
        {
            return $this->crdate;
        }


        /**
         * Returns the tstamp value
         *
         * @return int
         * @api
         */
        public function getTstamp(): int
        {
            return $this->tstamp;
        }


        /**
         * Returns the disable value
         *
         * @return bool
         */
        public function getDisable(): bool {
            return $this->disable;
        }


        /**
         * Returns the deleted value
         *
         * @return bool
         */
        public function getDeleted(): bool {
            return $this->deleted;
        }
    }

}
