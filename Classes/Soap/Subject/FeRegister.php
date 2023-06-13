<?php

namespace RKW\RkwSoap\Soap\Subject;

use RKW\RkwSoap\Utility\FilteredPropertiesUtility;
use Madj2k\CoreExtended\Utility\GeneralUtility as Common;
use Spipu\Html2Pdf\Debug\Debug;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * Class FeRegister
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FeRegister extends AbstractSubject
{

    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * frontendUserGroupRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * shippingAddressRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\ShippingAddressRepository
     */
    protected $shippingAddressRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected Logger $logger;


    /**
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function __construct()
    {
        parent::__construct();

        $this->buildSoapKeyArray(['fe_register']);

        if (ExtensionManagementUtility::isLoaded('fe_register')) {
            $this->shippingAddressRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\ShippingAddressRepository::class);
        }

        $this->frontendUserRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\FrontendUserRepository::class);
        $this->frontendUserGroupRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository::class);
    }


    /**
     * setStoragePidsToRepositories
     *
     * @return void
     * @throws InvalidConfigurationTypeException
     */
    public function setStoragePidsToRepositories(): void
    {
        if (ExtensionManagementUtility::isLoaded('fe_register')) {
            $this->shippingAddressRepository->setStoragePids($this->getStoragePids());
        }
        $this->frontendUserRepository->setStoragePids($this->getStoragePids());
        $this->frontendUserGroupRepository->setStoragePids($this->getStoragePids());
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findFeUsersByTimestamp(int $timestamp): array
    {
        try {
            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->frontendUserRepository->findByTimestamp($timestamp, false);

            // get basic data from shipping address if nothing is set in account
            if ($this->shippingAddressRepository) {

                /** @var \Madj2k\FeRegister\Domain\Model\FrontendUser $result */
                foreach ($results as $result) {
                    if (
                        (! $result->getFirstName())
                        && (! $result->getLastName())
                    ){

                        /** @var \Madj2k\FeRegister\Domain\Model\ShippingAddress $shippingAddress */
                        if ($shippingAddress = $this->shippingAddressRepository->findOneByFrontendUser($result->getUid())) {
                            $result->setTxFeregisterGender($shippingAddress->getGender());
                            $result->setFirstName($shippingAddress->getFirstName());
                            $result->setLastName($shippingAddress->getLastName());
                            $result->setAddress($shippingAddress->getAddress());
                            $result->setZip($shippingAddress->getZip());
                            $result->setCity($shippingAddress->getCity());
                            $result->setCompany($shippingAddress->getCompany());
                        }
                    }
                }
            }

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $this->soapKeyArray['fe_users']);
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return [];
    }


    /**
     * Returns a FE-users by uid
     *
     * @param int $uid
     * @return array
     */
    public function findFeUserByUid(int $uid): array
    {
        try {

            /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $result */
            $result = $this->frontendUserRepository->findByIdentifier(1);

            if ($result) {

                // get basic data from shipping address if nothing is set in account
                if ($this->shippingAddressRepository) {

                    /** @var \Madj2k\FeRegister\Domain\Model\FrontendUser $result */
                    if (
                        (! $result->getFirstName())
                        && (! $result->getLastName())
                    ){

                        /** @var \Madj2k\FeRegister\Domain\Model\ShippingAddress $shippingAddress */
                        if ($shippingAddress = $this->shippingAddressRepository->findOneByFrontendUser ($result->getUid())) {
                            $result->setTxFeregisterGender($shippingAddress->getGender());
                            $result->setFirstName($shippingAddress->getFirstName());
                            $result->setLastName($shippingAddress->getLastName());
                            $result->setAddress($shippingAddress->getAddress());
                            $result->setZip($shippingAddress->getZip());
                            $result->setCity($shippingAddress->getCity());
                            $result->setCompany($shippingAddress->getCompany());
                        }
                    }
                }

                return FilteredPropertiesUtility::filter($result, $this->soapKeyArray['fe_users']);
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return [];
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     *
     * @param int $timestamp
     * @param int $serviceOnly
     * @return array
     */
    public function findFeUserGroupsByTimestamp(int $timestamp, int $serviceOnly = 0): array
    {
        try {

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->frontendUserGroupRepository->findByTimestamp($timestamp, $serviceOnly);

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $this->soapKeyArray['fe_groups']);
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return [];
    }




    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return Common::getTypoScriptConfiguration('rkwsoap', $which);
    }




}

