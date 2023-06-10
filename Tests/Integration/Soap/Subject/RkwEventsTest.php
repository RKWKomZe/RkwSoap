<?php
namespace RKW\RkwSoap\Tests\Integration\Soap\Subject;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwSoap\Domain\Model\EventReservationAddPerson;
use RKW\RkwSoap\Domain\Repository\EventPlaceRepository;
use RKW\RkwSoap\Domain\Repository\EventRepository;
use RKW\RkwSoap\Domain\Repository\EventReservationAddPersonRepository;
use RKW\RkwSoap\Domain\Repository\EventReservationRepository;
use \RKW\RkwSoap\Soap\Server;
use \RKW\RkwSoap\Domain\Repository\FrontendUserRepository;

use \RKW\RkwSoap\Domain\Repository\ProductRepository;
use \RKW\RkwSoap\Domain\Repository\OrderRepository;
use \RKW\RkwSoap\Domain\Repository\OrderItemRepository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
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
 * RkwEventsTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwEventsTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RkwEventsTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/core_extended',
        'typo3conf/ext/postmaster',
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/fe_register',
        'typo3conf/ext/rkw_events',
        'typo3conf/ext/rkw_soap',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];


    /**
     * @var \RKW\RkwSoap\Soap\Subject\RkwEvents
     */
    private $subject = null;

    /**
     * @var \RKW\RkwSoap\Domain\Repository\EventRepository
     */
    private $eventRepository;

    /**
     * @var \RKW\RkwSoap\Domain\Repository\EventPlaceRepository
     */
    private $eventPlaceRepository;

    /**
     * @var \RKW\RkwSoap\Domain\Repository\EventReservationRepository
     */
    private $eventReservationRepository;

    /**
     * @var \RKW\RkwSoap\Domain\Repository\EventReservationAddPersonRepository
     */
    private $eventReservationAddPersonRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;



    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:postmaster/Configuration/TypoScript/setup.txt',
                'EXT:fe_register/Configuration/TypoScript/setup.txt',
                'EXT:rkw_events/Configuration/TypoScript/setup.txt',
                'EXT:rkw_soap/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->eventRepository = $this->objectManager->get(EventRepository::class);
        $this->eventPlaceRepository = $this->objectManager->get(EventPlaceRepository::class);
        $this->eventReservationRepository = $this->objectManager->get(EventReservationRepository::class);
        $this->eventReservationAddPersonRepository = $this->objectManager->get(EventReservationAddPersonRepository::class);

        $this->subject = $this->objectManager->get(Server::class);
     }


    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }








}
