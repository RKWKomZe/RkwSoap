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
     * @var string
     */
    protected string $storagePids = '';

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected Logger $logger;


    /**
     * @throws InvalidConfigurationTypeException
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);

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
     * Returns current storagePids
     *
     * @return string
     */
    public function getStoragePids(): string
    {
        return $this->storagePids;
    }


    /**
     * Set current storagePids
     *
     * @param string $storagePids
     * @return void
     */
    public function setStoragePids(string $storagePids): void
    {
        $this->storagePids = $storagePids;
        if (intval($this->getStoragePids()) > 0) {
            $this->setStoragePidsToRepositories();
        }
    }


    /**
     * setStoragePidsToRepositories
     * -> Use this function in extending classes to set the given PID(s) to one or more repositories
     * toDo: Other idea: Instead set PIDs so certain repositories, maybe set global to the Extbase repo for ALL repositories?
     *
     * @return void
     */
    public function setStoragePidsToRepositories(): void
    {
        // override and fill this inside your class
        // Example: $this->feUserRepository->setStoragePids($this->getStoragePids());
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

