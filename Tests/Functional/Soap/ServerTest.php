<?php
namespace RKW\RkwSoap\Tests\Functional\Soap;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use \RKW\RkwSoap\Soap\Server;
use \RKW\RkwShop\Domain\Repository\ProductRepository;
use \RKW\RkwShop\Domain\Repository\OrderRepository;

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
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    private $orderRepository;

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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/ShippingAddress.xml');



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

        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->orderRepository = $this->objectManager->get(OrderRepository::class);

        $this->subject = $this->objectManager->get(Server::class);
     }


    //=============================================

    /**
     * @test
     */
    public function rkwShopFindAllProducts_ReturnsProductsIncludingHiddenAndDeletedAndBundlesAndSubscriptions ()
    {

        $result = $this->subject->rkwShopFindAllProducts();
        $this::assertCount(7, $result);

    }

    /**
     * @test
     */
    public function rkwShopFindAllProducts_ReturnsProductsWithCumulatedStocks ()
    {

        $result = $this->subject->rkwShopFindAllProducts();

        $this::assertEquals(275, $result[0]['stock']);
        $this::assertEquals(50, $result[1]['stock']);
        $this::assertEquals(53, $result[2]['stock']);
        $this::assertEquals(54, $result[3]['stock']);
        $this::assertEquals(0, $result[4]['stock']);

    }

    /**
     * @test
     */
    public function rkwShopFindAllProducts_ReturnsProductsWithCommaSeparatedAdminEmails ()
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
    public function findAllPublications_ReturnsProductsIncludingHiddenAndDeletedButWithoutBundlesAndSubscriptions ()
    {

        $result = $this->subject->findAllPublications();
        $this::assertCount(4, $result);

    }


    /**
     * @test
     */
    public function findAllPublications_ReturnsProductsInBundlesAsSeries ()
    {

        $result = $this->subject->findAllPublications();
        $this::assertEquals(1, $result[0]['tx_rkwbasics_series']);

    }

    //=============================================

    /**
     * @test
     */
    public function findAllSeries_ReturnsProductsIncludingHiddenAndDeletedButOnlyBundlesAndSubscriptions ()
    {

        $result = $this->subject->findAllSeries();
        $this::assertCount(3, $result);

    }

    //=============================================

    /**
     * @test
     */
    public function rkwShopFindOrdersByTimestamp_GivenNothing_ReturnsOrdersIncludingHiddenAndDeletedAndIgnoresStoragePid()
    {
        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        $this::assertCount(6, $result);
    }


    /**
     * @test
     */
    public function rkwShopFindOrdersByTimestamp_GivenTimestamp_ReturnsOrdersWithTstampGreaterThanOrEqualGivenTimestamp()
    {
        $result = $this->subject->rkwShopFindOrdersByTimestamp(100);
        $this::assertCount(3, $result);
    }

    /**
     * @test
     */
    public function rkwShopFindOrdersByTimestamp_GivenNothing_ReturnsOrdersWithShippingAddressWithoutSubArray()
    {
        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        $this::assertEquals('Emmentaler Allee 15', $result[0]['address']);
    }


    //=============================================

    /**
     * @test
     */
    public function rkwShopFindOrderItemsByOrder_GivenOrderUid_ReturnsOrderItemsOfGivenOrderIodIncludingDeletedAndIgnoresStoragePid()
    {
        $result = $this->subject->rkwShopFindOrderItemsByOrder(20);
        $this::assertCount(3, $result);
    }

    /**
     * @test
     */
    public function rkwShopFindOrderItemsByOrder_GivenOrderUid_ReturnsOrderItemsWithReferencesToDeletedProducts()
    {
        $result = $this->subject->rkwShopFindOrderItemsByOrder(20);
        $this::assertEquals(3, $result[2]['product']);
    }

    //=============================================

    /**
     * @test
     */
    public function findOrdersByTimestamp_GivenTimestamp_ReturnsOrderListWithSubscribeValueSetForSubscriptions()
    {
        $result = $this->subject->findOrdersByTimestamp(10000);
        $this::assertEquals(true, $result[0]['subscribe']);
        $this::assertEquals(false, $result[0]['send_series']);

    }



    //=============================================
    /**
     * @test
     */
    public function rkwShopSetOrderedExternalForProduct_GivenExistingProductAndOrderedExternal_ReturnsTrueAndSetsGivenOrderedExternalTo_GivenProduct ()
    {

        static::assertTrue($this->subject->rkwShopSetOrderedExternalForProduct(1, 999));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        self::assertEquals(999, $product->getOrderedExternal());

    }

    //=============================================
    /**
     * @test
     */
    public function rkwShopSetStatusForOrder_GivenExistingOrderAndStatus_ReturnsTrueAndSetsGivenStatusToOrder ()
    {

        static::assertTrue($this->subject->rkwShopSetStatusForOrder(10, 100));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(10);
        self::assertEquals(100, $order->getStatus());

    }

    /**
     * @test
     */
    public function rkwShopSetStatusForOrder_GivenNonExistingOrderAndStatus_ReturnsFalse ()
    {

        static::assertFalse($this->subject->rkwShopSetStatusForOrder(99999999, 0));
    }


    /**
     * @test
     */
    public function rkwShopSetStatusForOrder_GivenExistingOrderAndInvalidStatus_ReturnsFalseAndDoesNotSetGivenStatusToOrder ()
    {

        static::assertFalse($this->subject->rkwShopSetStatusForOrder(10, 18));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(10);
        self::assertEquals(0, $order->getStatus());

    }



    //=============================================
    /**
     * @test
     */
    public function rkwShopSetDeletedForOrder_GivenExistingOrderAndStatus_ReturnsTrueAndSetsDeletedToOrder ()
    {

        static::assertTrue($this->subject->rkwShopSetDeletedForOrder(10, 1));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(10);
        self::assertEquals(1, $order->getDeleted());

    }

    /**
     * @test
     */
    public function rkwShopSetDeletedForOrder_GivenNonExistingOrderAndStatus_ReturnsFalse ()
    {
        static::assertFalse($this->subject->rkwShopSetDeletedForOrder(99999999, 1));

    }


    /**
     * @test
     */
    public function rkwShopSetDeletedForOrder_GivenExistingOrderAndInvalidStatus_ReturnsFalseAndDoesNotSetDeletedToOrder ()
    {

        static::assertFalse($this->subject->rkwShopSetDeletedForOrder(10, 5));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(10);
        self::assertEquals(0, $order->getDeleted());

    }



    //=============================================

    /**
     * @test
     */
    public function rkwShopAddStockForProduct_GivenExistingProductAndStockValue_ReturnsTrueAndAddsStockToProduct ()
    {

        static::assertTrue($this->subject->rkwShopAddStockForProduct(1, 5, 'Test', 111));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        $stock = $product->getStock()->toArray();

        self::assertCount(4, $stock);

        self::assertEquals(5, $stock[3]->getAmount());
        self::assertEquals('Test', $stock[3]->getComment());
        self::assertEquals(111, $stock[3]->getDeliveryStart());

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