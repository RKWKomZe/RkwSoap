<?php
namespace RKW\RkwSoap\Tests\Integration\Soap;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use \RKW\RkwSoap\Soap\Server;
use \RKW\RkwSoap\Domain\Repository\FrontendUserRepository;

use \RKW\RkwSoap\Domain\Repository\ProductRepository;
use \RKW\RkwSoap\Domain\Repository\OrderRepository;
use \RKW\RkwSoap\Domain\Repository\OrderItemRepository;

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
 * ServerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ServerTest extends FunctionalTestCase
{
    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ServerTest/Fixtures';

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
     * @var \RKW\RkwSoap\Soap\Server
     */
    private $subject = null;

    /**
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwSoap\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \RKW\RkwSoap\Domain\Repository\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \RKW\RkwSoap\Domain\Repository\OrderItemRepository
     */
    private $orderItemRepository;


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
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
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

        $this->subject = $this->objectManager->get(Server::class);
     }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getVersionReturnsConfiguredVersion ()
    {

        /**
         * Scenario:
         *
         * Given there is a version number configured
         * When I fetch the version number
         * Then the version number is returned
         */

        $this::assertEquals('0.8.15', $this->subject->getVersion());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getDifferentVersionReturnsAnotherConfiguredVersion ()
    {

        /**
         * Scenario:
         *
         * Given there is a different version number configured
         * When the method is called
         * Then the different version number is returned
         */
        FrontendSimulatorUtility::resetFrontendEnvironment();
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:postmaster/Configuration/TypoScript/setup.txt',
                'EXT:rkw_soap/Configuration/TypoScript/setup.txt',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage873.typoscript',
            ]
        );
        FrontendSimulatorUtility::simulateFrontendEnvironment(1);

        $this::assertNotEquals('9.5', $this->subject->getVersion());

    }


    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }








}
