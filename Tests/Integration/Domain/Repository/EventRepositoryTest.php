<?php
namespace RKW\RkwSoap\Tests\Integration\Domain\Repository;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwSoap\Domain\Repository\EventRepository;
use \RKW\RkwSoap\Soap\Server;
use \RKW\RkwSoap\Domain\Repository\FrontendUserRepository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
 * EventRepositoryTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EventRepositoryTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/EventRepositoryTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/persisted_sanitized_routing',
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
     * @var \RKW\RkwSoap\Domain\Repository\EventRepository
     */
    private $subject = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private ?ObjectManager $objectManager;


    /**
     * Setup
     *
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
                'EXT:static_info_tables/Configuration/TypoScript/setup.txt',
                'EXT:rkw_events/Configuration/TypoScript/setup.txt',
                'EXT:rkw_soap/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->subject = $this->objectManager->get(EventRepository::class);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventsByTimestampIgnoresEnableFieldsAndDeleted ()
    {
        /**
         * Scenario:
         *
         * Given there are three events
         * Given one event is hidden
         * Given one event is deleted
         * When the method is called
         * Then all three events are returned
         */
        $this->importDataSet(__DIR__ . '/EventRepositoryTest/Fixtures/Database/Check10.xml');


        $timestamp = 0;
        $result = $this->subject->findByTimestamp($timestamp);

        $this::assertCount(3, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventsByTimestampWithWrongPidReturnsNoResults ()
    {
        /**
         * Scenario:
         *
         * Given there are three events with another PID
         * When the method is called
         * Then NO events are returned
         */
        $this->importDataSet(__DIR__ . '/EventRepositoryTest/Fixtures/Database/Check20.xml');

        $timestamp = 0;
        $result = $this->subject->findByTimestamp($timestamp);

        $this::assertCount(0, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findEventsByTimestampWithMixedTimestampsReturnsTwoOfThreeResults ()
    {
        /**
         * Scenario:
         *
         * Given there are three events
         * Two with greater timestamps (timestamps "5" & "10")
         * One with less timestamp (timestamp "1")
         * When the method is called with timestamp "5"
         * Then two of three events are returned
         */
        $this->importDataSet(__DIR__ . '/EventRepositoryTest/Fixtures/Database/Check30.xml');

        $timestamp = 5;
        $result = $this->subject->findByTimestamp($timestamp);

        $this::assertCount(2, $result);
    }
}