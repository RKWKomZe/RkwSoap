<?php
namespace RKW\RkwSoap\Tests\Integration\Soap;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use \RKW\RkwSoap\Soap\Server;
use \RKW\RkwSoap\Domain\Repository\FrontendUserRepository;

use \RKW\RkwShop\Domain\Repository\ProductRepository;
use \RKW\RkwShop\Domain\Repository\OrderRepository;
use \RKW\RkwShop\Domain\Repository\OrderItemRepository;

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
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
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
    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Global.xml');
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
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

        $this->subject = $this->objectManager->get(Server::class);
     }


    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindAllProductsIgnoresEnableFields ()
    {

        /**
         * Scenario:
         *
         * Given there are three products
         * Given that one product is deleted
         * Given that one product is hidden
         * When I fetch the products
         * Then all three products are returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check10.xml');

        $result = $this->subject->rkwShopFindAllProducts();
        $this::assertCount(3, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindAllProductsRespectsStoragePid ()
    {

        /**
         * Scenario:
         *
         * Given there are two products
         * Given that one product has a different storage pid
         * When I fetch the products
         * Then only one of the products is returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check20.xml');

        $result = $this->subject->rkwShopFindAllProducts();
        $this::assertCount(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindAllProductsIncludesBundlesAndSubscriptions ()
    {
        /**
         * Scenario:
         *
         * Given there are three products
         * Given one of the products belongs to a bundle
         * Given one of the products is a subscription-product
         * When I fetch the products
         * Then all three products are returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check30.xml');

        $result = $this->subject->rkwShopFindAllProducts();
        $this::assertCount(3, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindAllProductsReturnsProductsWithCumulatedStocks ()
    {
        /**
         * Scenario:
         *
         * Given there are three products
         * Given each products has two stocks
         * When I fetch the products
         * Then the stocks of the products are cumulated
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check40.xml');

        $result = $this->subject->rkwShopFindAllProducts();

        $this::assertEquals(275, $result[0]['stock']);
        $this::assertEquals(50, $result[1]['stock']);
        $this::assertEquals(53, $result[2]['stock']);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindAllProductsReturnsProductsWithCommaSeparatedAdminEmails ()
    {

        /**
         * Scenario:
         *
         * Given there are three products
         * Given each products has several admins
         * When I fetch the products
         * Then the admin mails are returned as comma separated list
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check50.xml');

        $result = $this->subject->rkwShopFindAllProducts();

        $this::assertEquals('test1@test.de,test2@test.de,test3@test.de', $result[0]['backend_user']);
        $this::assertEquals('0', $result[1]['backend_user']);
        $this::assertEquals('test1@test.de,test2@test.de', $result[2]['backend_user']);

    }


    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindOrdersByTimestampIgnoresEnableFieldsAndDeleted ()
    {
        /**
         * Scenario:
         *
         * Given there are three orders
         * Given one order is hidden
         * Given one order is deleted
         * When I fetch the order
         * Then all three orders are returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check60.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        $this::assertCount(3, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindOrdersByTimestampRespectsStoragePid()
    {
        /**
         * Scenario:
         *
         * Given there are two orders
         * Given one order has a different storage pid
         * When I fetch the order
         * Then only one product is returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check70.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        $this::assertCount(1, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindOrdersByTimestampReturnsOrdersWithTstampGreaterThanOrEqualToGivenTimestamp()
    {

        /**
         * Scenario:
         *
         * Given there are three orders
         * Given one order has a tstamp lower than 100
         * Given one order has a tstamp equal to 100
         * Given one order has a tstamp higher than 100
         * When I fetch the order with timestamp = 100
         * Then two orders are returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check80.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp(100);
        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindOrdersByTimestampReturnsShippingAddressInParentObject()
    {

        /**
         * Scenario:
         *
         * Given there is a order
         * Given that order has a shipping address
         * When I fetch the order
         * Then the order is returned with the shipping address included in the parent object
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check81.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        $this::assertEquals('Emmentaler Allee 15', $result[0]['address']);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindOrdersByTimestampIncludesReferencesToDisabledAndDeletedFeUsers()
    {

        /**
         * Scenario:
         *
         * Given there are three orders
         * Given that one order belongs to disabled frontend user
         * Given that one order belongs to a deleted frontend user
         * When I fetch the orders
         * Then all three orders are returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check90.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        static::assertCount(3, $result);

        static::assertGreaterThan(0, $result[0]['frontend_user']);
        static::assertGreaterThan(0, $result[1]['frontend_user']);
        static::assertGreaterThan(0, $result[2]['frontend_user']);


    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     **/
    public function rkwShopFindOrderItemsByOrderIncludesDeletedOrderItems()
    {
        /**
         * Scenario:
         *
         * Given there is an order
         * Given that order has three order items
         * Given one of the order items is deleted
         * When I fetch the order-items for that order
         * Then both order items are returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check100.xml');

        $result = $this->subject->rkwShopFindOrderItemsByOrder(1);
        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     **/
    public function rkwShopFindOrderItemsByOrderRespectsStoragePid()
    {
        /**
         * Scenario:
         *
         * Given there is an order
         * Given that order has three order items
         * Given one of the order items has a different storage pid
         * When I fetch the order-items for that order
         * Then ony one order item is returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check110.xml');

        $result = $this->subject->rkwShopFindOrderItemsByOrder(1);
        $this::assertCount(1, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopFindOrderItemsByOrderKeepsReferencesToDeletedAndHiddenProducts()
    {
        /**
         * Scenario:
         *
         * Given there is an order
         * Given that order has three order items
         * Given one of the order items has a reference to a deleted product
         * Given one of the order items has a reference to a hidden product
         * When I fetch the order-items for that order
         * Then both order items are returned
         * Then for both order items the reference to the products are kept
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check120.xml');

        $result = $this->subject->rkwShopFindOrderItemsByOrder(1);
        $this::assertCount(3, $result);

        $this::assertEquals(1, $result[0]['product']);
        $this::assertEquals(2, $result[1]['product']);
        $this::assertEquals(3, $result[2]['product']);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetOrderedExternalForProductStoresGivenValue ()
    {

        /**
         * Scenario:
         *
         * Given there is a product
         * When I set the external order for that product
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check130.xml');
        static::assertTrue($this->subject->rkwShopSetOrderedExternalForProduct(1, 999));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        self::assertEquals(999, $product->getOrderedExternal());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetOrderedExternalForProductStoresGivenValueIfProductIsDeleted ()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted product
         * When I set the external order for that product
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check131.xml');
        static::assertTrue($this->subject->rkwShopSetOrderedExternalForProduct(1, 999));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        self::assertEquals(999, $product->getOrderedExternal());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetOrderedExternalForProductStoresGivenValueIfProductIsHidden ()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted product
         * When I set the external order for that product
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check132.xml');
        static::assertTrue($this->subject->rkwShopSetOrderedExternalForProduct(1, 999));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        self::assertEquals(999, $product->getOrderedExternal());

    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetStatusForOrderSetsGivenStatus ()
    {

        /**
         * Scenario:
         *
         * Given there is a order
         * When I set a valid status for that order
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check140.xml');

        static::assertTrue($this->subject->rkwShopSetStatusForOrder(1, 100));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);
        self::assertEquals(100, $order->getStatus());
    }


     /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetStatusForOrderChecksForValidStatusCodes ()
    {
        /**
         * Scenario:
         *
         * Given there is a order
         * When I set an invalid status for that order
         * Then false is returned
         * Then the value is not stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check140.xml');

        static::assertFalse($this->subject->rkwShopSetStatusForOrder(1, 99999));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);
        self::assertEquals(0, $order->getStatus());
    }


    /**
     * @test
     */
    public function rkwShopSetStatusForOrderChecksForExistingOrders ()
    {
        /**
         * Scenario:
         *
         * Given there no order
         * When I set a valid status for a non-existing order
         * Then false is returned
         */
        static::assertFalse($this->subject->rkwShopSetStatusForOrder(1, 100));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetStatusForOrderSetsGivenStatusIfOrderIsDeleted ()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted order
         * When I set a valid status for that order
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check150.xml');

        static::assertTrue($this->subject->rkwShopSetStatusForOrder(1, 100));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);
        self::assertEquals(100, $order->getStatus());

    }


    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetDeletedForOrderDeletesGivenOrder ()
    {
        /**
         * Scenario:
         *
         * Given there is an order
         * When I set that order as deleted
         * Then true is returned
         * Then the order is set to deleted
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check160.xml');

        static::assertTrue($this->subject->rkwShopSetDeletedForOrder(1, 1));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);
        self::assertEquals(1, $order->getDeleted());

    }

    /**
     * @test
     */
    public function rkwShopSetDeletedForOrderChecksForExistingOrders ()
    {

        /**
         * Scenario:
         *
         * Given there is no order
         * When I set a set a non-existing order to deleted
         * Then false is returned
         */
        static::assertFalse($this->subject->rkwShopSetDeletedForOrder(1, 1));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetDeletedForOrderChecksForValidValues ()
    {

        /**
         * Scenario:
         *
         * Given there is an order
         * When I set that order as deleted with an invalid value
         * Then false is returned
         * Then the order is not set to deleted
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check160.xml');

        static::assertFalse($this->subject->rkwShopSetDeletedForOrder(1, 5));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);
        self::assertEquals(0, $order->getDeleted());

    }



    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopAddStockForProductAddsAStock ()
    {
        /**
         * Scenario:
         *
         * Given there is a product
         * When I add a stock to the product
         * Then true is returned
         * Then the stock is added with all given values
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check170.xml');

        static::assertTrue($this->subject->rkwShopAddStockForProduct(1, 5, 'Test', 111));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        $stock = $product->getStock()->toArray();

        self::assertCount(2, $stock);

        self::assertEquals(5, $stock[1]->getAmount());
        self::assertEquals('Test', $stock[1]->getComment());
        self::assertEquals(111, $stock[1]->getDeliveryStart());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopAddStockForProductAddsAStockIfTheProductIsDeleted ()
    {
        /**
         * Scenario:
         *
         * Given there is a product
         * When I add a stock to the product
         * Then true is returned
         * Then the stock is added with all given values
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check180.xml');

        static::assertTrue($this->subject->rkwShopAddStockForProduct(1, 5, 'Test', 111));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        $stock = $product->getStock()->toArray();

        self::assertCount(2, $stock);

        self::assertEquals(5, $stock[1]->getAmount());
        self::assertEquals('Test', $stock[1]->getComment());
        self::assertEquals(111, $stock[1]->getDeliveryStart());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopAddStockForProductAddsAStockIfTheProductIsHidden ()
    {
        /**
         * Scenario:
         *
         * Given there is a product
         * When I add a stock to the product
         * Then true is returned
         * Then the stock is added with all given values
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check190.xml');

        static::assertTrue($this->subject->rkwShopAddStockForProduct(1, 5, 'Test', 111));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        $stock = $product->getStock()->toArray();

        self::assertCount(2, $stock);

        self::assertEquals(5, $stock[1]->getAmount());
        self::assertEquals('Test', $stock[1]->getComment());
        self::assertEquals(111, $stock[1]->getDeliveryStart());

    }



    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetStatusForOrderItemSetsGivenStatus ()
    {

        /**
         * Scenario:
         *
         * Given there is a order item
         * When I set a valid status for that order item
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check200.xml');

        static::assertTrue($this->subject->rkwShopSetStatusForOrderItem(1, 100));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = $this->orderItemRepository->findByUid(1);
        self::assertEquals(100, $orderItem->getStatus());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetStatusForOrderItemChecksForValidStatusCodes ()
    {
        /**
         * Scenario:
         *
         * Given there is a order item
         * When I set an invalid status for that order item
         * Then false is returned
         * Then the value is not stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check200.xml');

        static::assertFalse($this->subject->rkwShopSetStatusForOrderItem(1, 99999));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = $this->orderItemRepository->findByUid(1);
        self::assertEquals(0, $orderItem->getStatus());
    }


    /**
     * @test
     */
    public function rkwShopSetStatusForOrderItemChecksForExistingOrderItems ()
    {
        /**
         * Scenario:
         *
         * Given there no order item
         * When I set a valid status for a non-existing order item
         * Then false is returned
         */
        static::assertFalse($this->subject->rkwShopSetStatusForOrderItem(1, 100));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function rkwShopSetStatusForOrderItemSetsGivenStatusIfOrderItemIsDeleted ()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted order item
         * When I set a valid status for that order item
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check210.xml');

        static::assertTrue($this->subject->rkwShopSetStatusForOrderItem(1, 100));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = $this->orderItemRepository->findByUid(1);
        self::assertEquals(100, $orderItem->getStatus());

    }

    //=============================================



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
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check220.xml');

        $result = $this->subject->findFeUserByUid(1);

        static::assertNotNull($result);
        static::assertEquals(1, $result['uid']);

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
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check230.xml');

        $result = $this->subject->findFeUserByUid(1);

        static::assertNotNull($result);
        static::assertEquals(1, $result['uid']);

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
         * Given there is a frontend user
         * Given the frontend user has a different storage pid
         * When I fetch the frontend user by uid
         * Then the frontend user is returned
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check240.xml');

        $result = $this->subject->findFeUserByUid(1);

        static::assertNotNull($result);
        static::assertEquals(1, $result['uid']);

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
         * Given there is a frontend user
         * Given the frontend user has a shipping address
         * Given the frontend user has a first name and a last name set
         * Given the frontend user has address details
         * When I fetch the frontend user by uid
         * Then the details of the frontend user are returned
         * Then the shipping address of the frontend user is ignored
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check250.xml');

        $result = $this->subject->findFeUserByUid(1);

        static::assertNotNull($result);
        static::assertEquals(1, $result['uid']);
        static::assertEquals('Karl', $result['first_name']);
        static::assertEquals('Lauterbach', $result['last_name']);
        static::assertEquals('SPD', $result['company']);
        static::assertEquals('Wilhelmstraße 141', $result['address']);
        static::assertEquals('10963', $result['zip']);
        static::assertEquals('Berlin', $result['city']);

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
         * Given there is a frontend user
         * Given the frontend user has a shipping address
         * Given the frontend user has no first name and no last name set
         * Given the frontend user has address details
         * When I fetch the frontend user by uid
         * Then the shipping address overrides name and address data
         */
        $this->importDataSet(__DIR__ . '/ServerTest/Fixtures/Database/Check260.xml');

        $result = $this->subject->findFeUserByUid(1);

        static::assertNotNull($result);
        static::assertEquals(1, $result['uid']);
        static::assertEquals('Johannes', $result['first_name']);
        static::assertEquals('Spacko', $result['last_name']);
        static::assertEquals('', $result['company']);
        static::assertEquals('Emmentaler Allee 15', $result['address']);
        static::assertEquals('12345', $result['zip']);
        static::assertEquals('Gauda', $result['city']);

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