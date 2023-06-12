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
 * Class RkwEvents
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwEvents extends AbstractSubject
{

    /**
     * eventRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\EventRepository
     */
    protected $eventRepository;


    /**
     * eventPlaceRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\EventPlaceRepository
     */
    protected $eventPlaceRepository;


    /**
     * eventReservationRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\EventReservationRepository
     */
    protected $eventReservationRepository;


    /**
     * eventReservationAddPersonRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\EventReservationAddPersonRepository
     */
    protected $eventReservationAddPersonRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;



    public function __construct()
    {
        parent::__construct();

        $this->buildSoapKeyArray(['rkw_events']);

        if (ExtensionManagementUtility::isLoaded('rkw_events')) {
            $this->eventRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\EventRepository::class);
            $this->eventPlaceRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\EventPlaceRepository::class);
            $this->eventReservationRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\EventReservationRepository::class);
            $this->eventReservationAddPersonRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\EventReservationAddPersonRepository::class);
        }

        $this->persistenceManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);

    }

    /**
     * Returns all existing events by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventsByTimestamp(int $timestamp): array
    {
        try {

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->eventRepository->findByTimestamp($timestamp);

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $this->soapKeyArray['tx_rkwevents_domain_model_event']);
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return [];

    }

    /**
     * Returns all existing eventPlaces by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventPlacesByTimestamp(int $timestamp): array
    {
        try {

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->eventPlaceRepository->findByTimestamp($timestamp);

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $this->soapKeyArray['tx_rkwevents_domain_model_eventplace']);
            }


        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return [];
    }


    /**
     * Returns all existing eventReservations by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventReservationsByTimestamp(int $timestamp): array
    {
        try {

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->eventReservationRepository->findByTimestamp($timestamp);

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $this->soapKeyArray['tx_rkwevents_domain_model_eventreservation']);
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return [];
    }


    /**
     * Returns all existing eventReservationAddPersons by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventReservationAddPersonsByTimestamp(int $timestamp): array
    {
        try {

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->eventReservationAddPersonRepository->findByTimestamp($timestamp);

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $this->soapKeyArray['tx_rkwevents_domain_model_eventreservationaddperson']);
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

