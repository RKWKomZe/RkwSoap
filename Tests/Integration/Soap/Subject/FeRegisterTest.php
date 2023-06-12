<?php
namespace RKW\RkwSoap\Tests\Integration\Soap\Subject;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use \RKW\RkwSoap\Soap\Server;
use \RKW\RkwSoap\Domain\Repository\FrontendUserRepository;

use \RKW\RkwSoap\Domain\Repository\ProductRepository;
use \RKW\RkwSoap\Domain\Repository\OrderRepository;
use \RKW\RkwSoap\Domain\Repository\OrderItemRepository;

use RKW\RkwSoap\Soap\Subject\FeRegister;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Madj2k\CoreExtended\Utility\FrontendSimulatorUtility;
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
 * FeRegisterTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FeRegisterTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FeRegisterTest/Fixtures';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/core_extended',
        'typo3conf/ext/postmaster',
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/fe_register',
        'typo3conf/ext/rkw_shop',
        'typo3conf/ext/rkw_soap',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];


    /**
     * @var \RKW\RkwSoap\Soap\Subject\FeRegister
     */
    private $subject = null;


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
                'EXT:rkw_soap/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

        $this->subject = $this->objectManager->get(FeRegister::class);
     }


    /**
     * @test
     * @throws \Exception
     */
    public function findFeUserByUidIncludesDeletedFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user is deleted
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $result = $this->subject->findFeUserByUid(1);

        self::assertNotNull($result);
        self::assertEquals(1, $result['uid']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findFeUserByUidIncludesDisabledFrontendUser()
    {

        /**
         * Scenario:
         *
         * Given there is a frontend user
         * Given the frontend user is disabled
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $result = $this->subject->findFeUserByUid(1);

        self::assertNotNull($result);
        self::assertEquals(1, $result['uid']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findFeUserByUidIgnoresStoragePid ()
    {

        /**
         * Scenario:
         *
         * !! BY MF: THIS IS NOT LONGER A VALID TEST FOR THE NEW RKW_SOAP VERSION !!
         * !! Is now simply checking if there is nothing returned
         *
         * Given there is a frontend user
         * Given the frontend user has a different storage pid
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $result = $this->subject->findFeUserByUid(1);

        self::assertCount(0, $result);
        //self::assertNotNull($result);
        //self::assertEquals(1, $result['uid']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findFeUserByUidReturnsAddressDetails ()
    {

        /**
         * Scenario:
         *
         * !! By MF: THIS TEST WORKS NOW WITH EQUAL PIDs !!
         *
         * Given there is a frontend user
         * Given the frontend user has a shipping address
         * Given the frontend user has a first name and a last name set
         * Given the frontend user has address details
         * When I fetch the frontend user by uid
         * Then the details of the frontend user are returned
         * Then the shipping address of the frontend user is ignored
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        $result = $this->subject->findFeUserByUid(1);

        self::assertNotNull($result);
        self::assertEquals(1, $result['uid']);
        self::assertEquals('Karl', $result['first_name']);
        self::assertEquals('Lauterbach', $result['last_name']);
        self::assertEquals('SPD', $result['company']);
        self::assertEquals('Wilhelmstraße 141', $result['address']);
        self::assertEquals('10963', $result['zip']);
        self::assertEquals('Berlin', $result['city']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function findFeUserByUidReturnsShippingAddressIfThereIsNoNameSet()
    {

        /**
         * Scenario:
         *
         * !! By MF: THIS TEST WORKS NOW WITH EQUAL PIDs !!
         *
         * Given there is a frontend user
         * Given the frontend user has a shipping address
         * Given the frontend user has no first name and no last name set
         * Given the frontend user has address details
         * When I fetch the frontend user by uid
         * Then the shipping address overrides name and address data
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        $result = $this->subject->findFeUserByUid(1);

        self::assertNotNull($result);
        self::assertEquals(1, $result['uid']);
        self::assertEquals('Johannes', $result['first_name']);
        self::assertEquals('Spacko', $result['last_name']);
        self::assertEquals('', $result['company']);
        self::assertEquals('Emmentaler Allee 15', $result['address']);
        self::assertEquals('12345', $result['zip']);
        self::assertEquals('Gauda', $result['city']);

    }
    //=============================================

    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }








}
