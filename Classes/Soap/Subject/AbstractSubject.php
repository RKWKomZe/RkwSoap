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
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
 * Class AbstractSubject
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractSubject
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    protected array $settings;

    /**
     * @var array
     */
    protected array $soapKeyArray;


    /**
     * @throws InvalidConfigurationTypeException
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->persistenceManager =  $this->objectManager->get(PersistenceManager::class);

        $this->settings = $this->getSettings();
    }


    /**
     * Creates finalized key array depends on the server version and the installed extensions
     *
     * @param array $optionalExtensionKeyList
     * @return void
     */
    protected function buildSoapKeyArray(array $optionalExtensionKeyList): void
    {
        foreach ($this->settings['keys'] as $tableName => $keyDefinitions) {

            // 1. Set basic keys
            $this->soapKeyArray[$tableName] = $keyDefinitions['default'];

            // 2. Override, if override definition exists AND the required extensions are installed
            foreach ($optionalExtensionKeyList as $extensionKey) {
                if (ExtensionManagementUtility::isLoaded($extensionKey)) {
                    if (key_exists($extensionKey, $this->settings['keys'][$tableName])) {
                        $this->soapKeyArray[$tableName] = array_merge(
                            $this->soapKeyArray[$tableName],
                            $this->settings['keys'][$tableName][$extensionKey]
                        );
                    }
                }
            }

            // 3. Check server version for possible overrides in relation of backward compatibility of older versions
            $soapServerVersionWithSlash = str_replace('.', '\\', $this->settings['soapServer']['version']);
            if (
                key_exists('legacy', $this->settings['keys'][$tableName])
                && key_exists($soapServerVersionWithSlash, $this->settings['keys'][$tableName]['legacy'])
            ) {
                // 3.1 general default fields override
                if (key_exists('default', $this->settings['keys'][$tableName]['legacy'][$soapServerVersionWithSlash])) {
                    $this->soapKeyArray[$tableName] = array_merge(
                        $this->soapKeyArray[$tableName],
                        $this->settings['keys'][$tableName]['legacy'][$soapServerVersionWithSlash]['default']
                    );
                }

                // 3.2 extension specific override
                foreach ($optionalExtensionKeyList as $extensionKey) {
                    if (ExtensionManagementUtility::isLoaded($extensionKey)) {
                        if (key_exists(
                            $extensionKey,
                            $this->settings['keys'][$tableName]['legacy'][$soapServerVersionWithSlash])
                        ) {
                            $this->soapKeyArray[$tableName] = array_merge(
                                $this->soapKeyArray[$tableName],
                                $this->settings['keys'][$tableName]['legacy'][$soapServerVersionWithSlash][$extensionKey]
                            );
                        }
                    }
                }
            }
        }

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

            $keys = array(
                'uid',
                'crdate',
                'tstamp',
                'disable',
                'deleted',
                'username',
                'usergroup',
                'company',
                'first_name',
                'middle_name',
                'last_name',
                'address',
                'zip',
                'city',
                'telephone',
                'fax',
                'email',
                'www',
            );

            if (ExtensionManagementUtility::isLoaded('fe_register')) {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'disable',
                    'deleted',
                    'username',
                    'usergroup',
                    'company',
                    'tx_feregister_gender',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'address',
                    'zip',
                    'city',
                    'telephone',
                    'fax',
                    'email',
                    'www',
                    'tx_feregister_facebook_url',
                    'tx_feregister_twitter_url',
                    'tx_feregister_xing_url',

                );
            }

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
            }

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $keys);
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

            $keys = array(
                'uid',
                'crdate',
                'tstamp',
                'disable',
                'deleted',
                'username',
                'usergroup',
                'company',
                'first_name',
                'middle_name',
                'last_name',
                'address',
                'zip',
                'city',
                'telephone',
                'fax',
                'email',
                'www',
            );

            if (ExtensionManagementUtility::isLoaded('fe_register')) {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'disable',
                    'deleted',
                    'username',
                    'usergroup',
                    'company',
                    'tx_feregister_gender',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'address',
                    'zip',
                    'city',
                    'telephone',
                    'fax',
                    'email',
                    'www',
                    'tx_feregister_facebook_url' => 'keyFuerAusgabe',
                    'tx_feregister_twitter_url',
                    'tx_feregister_xing_url',

                );
            }

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

                return FilteredPropertiesUtility::filter($result, $keys);
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

            $keys = array(
                'uid',
                'crdate',
                'tstamp',
                'hidden',
                'deleted',
                'title',
                'description',
            );

            if (ExtensionManagementUtility::isLoaded('fe_register')) {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'title',
                    'description',
                    'tx_feregister_is_service',
                );
            }

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->frontendUserGroupRepository->findByTimestamp($timestamp, $serviceOnly);

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $keys);
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

