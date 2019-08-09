<?php
namespace RKW\RkwSoap\Tests\Functional\Soap;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use \RKW\RkwSoap\Soap\Server;

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
 * ServerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ServerTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_shop',
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
    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/Fixtures/Database/Pages.xml');


        $this->importDataSet(__DIR__ . '/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/Stock.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_soap/Configuration/TypoScript/setup.txt',
                'EXT:rkw_soap/Tests/Functional/Soap/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );


        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->subject = $this->objectManager->get(Server::class);
     }


    //=============================================

    /**
     * @test
     */
    public function rkwShopFindAllProductsReturnsProductsIncludingHiddenAndDeletedAndBundlesAndSubscriptions ()
    {

        $result = $this->subject->rkwShopFindAllProducts();
        $this::assertCount(5, $result);

    }

    /**
     * @test
     */
    public function rkwShopFindAllProductsReturnsProductsWithCumulatedStocks ()
    {

        $result = $this->subject->rkwShopFindAllProducts();

        $this::assertEquals(275, $result[0]['stock']);
        $this::assertEquals(50, $result[1]['stock']);
        $this::assertEquals(53, $result[2]['stock']);
        $this::assertEquals(54, $result[3]['stock']);
        $this::assertEquals(19, $result[4]['stock']);

    }

    /**
     * @test
     */
    public function rkwShopFindAllProductsReturnsProductsWithCommaSeparatedAdminEmails ()
    {

        $result = $this->subject->rkwShopFindAllProducts();

        $this::assertEquals('test1@test.de,test2@test.de,test3@test.de', $result[0]['backend_user']);
        $this::assertEquals('test4@test.de', $result[1]['backend_user']);
        $this::assertEquals('test1@test.de,test2@test.de', $result[2]['backend_user']);
        $this::assertEquals('test3@test.de', $result[3]['backend_user']);
        $this::assertEquals('test2@test.de', $result[4]['backend_user']);

    }

    //=============================================

    /**
     * @test
     */
    public function findAllPublicationsReturnsProductsIncludingHiddenAndDeletedButWithoutBundlesAndSubscriptions ()
    {

        $result = $this->subject->findAllPublications();
        $this::assertCount(3, $result);

    }


    /**
     * @test
     */
    public function findAllPublicationsReturnsProductsInBundlesAsSeries ()
    {

        $result = $this->subject->findAllPublications();
        $this::assertEquals(1, $result[0]['tx_rkwbasics_series']);

    }

    //=============================================

    /**
     * @test
     */
    public function findAllSeriesReturnsProductsIncludingHiddenAndDeletedButOnlyBundlesAndSubscriptions ()
    {

        $result = $this->subject->findAllSeries();
        $this::assertCount(2, $result);

    }

    //=============================================

    /**
     * @test
     */
    public function rkwShopFindOrdersByTimestampReturnsOrdersIncludingHiddenAndDeleted()
    {

        $result = $this->subject->rkwShopFindOrdersByTimestamp(1);
        $this::assertCount(5, $result);

    }

    //=============================================


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}