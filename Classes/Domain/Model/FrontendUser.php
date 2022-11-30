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

/**
 * Class FrontendUser
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
{

    /**
     * @var integer
     */
    protected $crdate;

    /**
     * @var integer
     */
    protected $tstamp;

    /**
     * @var integer
     */
    protected $disable;

    /**
     * @var integer
     */
    protected $deleted;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwSoap\Domain\Model\FrontendUserGroup>
     */
    protected $usergroup;

    /**
     * initialize objectStorage
     *
     */
    public function __construct() {
        parent::__construct();
        $this->usergroup = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }


    /**
     * Returns the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage An object storage containing the usergroup
     * @api
     */
    public function getUsergroup() {
        return $this->usergroup;
        //===
    }


    /**
     * Returns the crdate value
     *
     * @return integer
     * @api
     */
    public function getCrdate() {

        return $this->crdate;
        //===
    }


    /**
     * Returns the tstamp value
     *
     * @return integer
     * @api
     */
    public function getTstamp() {
        return $this->tstamp;
        //===
    }


    /**
     * Returns the disable value
     *
     * @return integer
     */
    public function getDisable() {
        return $this->disable;
        //===
    }


    /**
     * Returns the deleted value
     *
     * @return integer
     *
     */
    public function getDeleted() {
        return $this->deleted;
        //===
    }
}
