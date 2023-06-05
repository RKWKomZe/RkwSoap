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

/**
 * Class FrontendUserGroup
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
if (ExtensionManagementUtility::isLoaded('fe_register')) {
    class FrontendUserGroup extends \Madj2k\FeRegister\Domain\Model\FrontendUserGroup
    {

    }
} else {
    class FrontendUserGroup extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup
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
        protected bool $hidden = false;


        /**
         * @var bool
         */
        protected bool $deleted = false;


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
         * Returns the hidden value
         *
         * @return bool
         * @api
         */
        public function getHidden(): bool
        {
            return $this->hidden;
        }


        /**
         * Returns the deleted value
         *
         * @return bool
         * @api
         */
        public function getDeleted(): bool
        {
            return $this->deleted;
        }
    }
}
