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

use RKW\RkwSoap\Soap\Subject\RkwEvents;
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
        'typo3conf/ext/static_info_tables',
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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
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

        $this->subject = $this->objectManager->get(RkwEvents::class);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventsByTimestampIgnoresEnableFields ()
    {

        /**
         * Scenario:
         *
         * Given there are three events
         * Given that are all events have a greater timestamp
         * Given that one event is deleted
         * Given that one event is hidden
         * When the method is called
         * Then all three events are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $result = $this->subject->findEventsByTimestamp(10);

        $this::assertCount(3, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventsByTimestampWithVariousTimestamps ()
    {

        /**
         * Scenario:
         *
         * Given there are three events
         * Given that one event timestamp is greater
         * Given that one event timestamp is equal
         * Given that one event timestamp is less
         * When the method is called
         * Then two of three events are returned (the greater and the equal one)
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $result = $this->subject->findEventsByTimestamp(10);

        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventsByTimestampWithVariousStoragePids ()
    {

        /**
         * Scenario:
         *
         * Given there are three events
         * Given that are all events have a greater timestamp
         * Given that two events have PID 1
         * Given that one event have PID 2
         * When the method is called
         * Then two of three events are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $result = $this->subject->findEventsByTimestamp(10);

        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventPlacesByTimestampIgnoresEnableFields ()
    {

        /**
         * Scenario:
         *
         * Given there are three eventPlaces
         * Given that are all eventPlaces have a greater timestamp
         * Given that one eventPlace is deleted
         * Given that one eventPlace is hidden
         * When the method is called
         * Then all three eventPlaces are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        $result = $this->subject->findEventPlacesByTimestamp(10);

        $this::assertCount(3, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventPlacesByTimestampWithVariousTimestamps ()
    {

        /**
         * Scenario:
         *
         * Given there are three eventPlaces
         * Given that one eventPlace timestamp is greater
         * Given that one eventPlace timestamp is equal
         * Given that one eventPlace timestamp is less
         * When the method is called
         * Then two of three eventPlaces are returned (the greater and the equal one)
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check110.xml');

        $result = $this->subject->findEventPlacesByTimestamp(10);

        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventPlacesByTimestampWithVariousStoragePids ()
    {

        /**
         * Scenario:
         *
         * Given there are three eventPlaces
         * Given that are all eventPlaces have a greater timestamp
         * Given that two eventPlaces have PID 1
         * Given that one eventPlace have PID 2
         * When the method is called
         * Then two of three eventPlaces are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check120.xml');

        $result = $this->subject->findEventPlacesByTimestamp(120);

        $this::assertCount(2, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function findEventReservationsByTimestampIgnoresEnableFields ()
    {
        /**
         * Scenario:
         *
         * Given there are three eventReservations
         * Given that are all eventReservations have a greater timestamp
         * Given that two eventReservations are deleted
         * When the method is called
         * Then all three eventReservations are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');

        $result = $this->subject->findEventReservationsByTimestamp(10);

        $this::assertCount(3, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventReservationsByTimestampWithVariousTimestamps ()
    {

        /**
         * Scenario:
         *
         * Given there are three eventReservations
         * Given that one eventReservation timestamp is greater
         * Given that one eventReservation timestamp is equal
         * Given that one eventReservation timestamp is less
         * When the method is called
         * Then two of three eventReservations are returned (the greater and the equal one)
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check210.xml');

        $result = $this->subject->findEventReservationsByTimestamp(10);

        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventReservationsByTimestampWithVariousStoragePids ()
    {

        /**
         * Scenario:
         *
         * Given there are three eventReservations
         * Given that are all eventReservations have a greater timestamp
         * Given that two eventReservations have PID 1
         * Given that one eventReservation have PID 2
         * When the method is called
         * Then two of three eventReservations are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check220.xml');

        $result = $this->subject->findEventReservationsByTimestamp(10);

        $this::assertCount(2, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function findEventReservationAddPersonsByTimestampIgnoresEnableFields ()
    {
        /**
         * Scenario:
         *
         * Given there are three eventReservationAddPersons
         * Given that are all eventReservationAddPersons have a greater timestamp
         * Given that two eventReservationAddPersons are deleted
         * When the method is called
         * Then all three eventReservationAddPersons are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check300.xml');

        $result = $this->subject->findEventReservationAddPersonsByTimestamp(10);

        $this::assertCount(3, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventReservationAddPersonsByTimestampWithVariousTimestamps ()
    {

        /**
         * Scenario:
         *
         * Given there are three eventReservationAddPersons
         * Given that one eventReservationAddPerson timestamp is greater
         * Given that one eventReservationAddPerson timestamp is equal
         * Given that one eventReservationAddPerson timestamp is less
         * When the method is called
         * Then two of three eventReservationAddPersons are returned (the greater and the equal one)
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check310.xml');

        $result = $this->subject->findEventReservationAddPersonsByTimestamp(10);

        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventReservationAddPersonsByTimestampWithVariousStoragePids ()
    {

        /**
         * Scenario:
         *
         * Given there are three eventReservationAddPersons
         * Given that are all eventReservationAddPersons have a greater timestamp
         * Given that two eventReservationAddPersons have PID 1
         * Given that one eventReservationAddPerson have PID 2
         * When the method is called
         * Then two of three eventReservationAddPersons are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check320.xml');

        $result = $this->subject->findEventReservationAddPersonsByTimestamp(10);

        $this::assertCount(2, $result);
    }

    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }



}
