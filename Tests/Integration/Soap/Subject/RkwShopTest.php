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
 * RkwShopTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwShopTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RkwShopTest/Fixtures';

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
     * @var \RKW\RkwSoap\Soap\Subject\RkwShop
     */
    private $subject = null;

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

        $this->subject = $this->objectManager->get(Server::class);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findAllProductsIgnoresEnableFields ()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $result = $this->subject->rkwShopFindAllProducts();

        $this::assertCount(3, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllProductsRespectsStoragePid ()
    {

        /**
         * Scenario:
         *
         * Given there are two products
         * Given that one product has a different storage pid
         * When I fetch the products
         * Then only one of the products is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $result = $this->subject->rkwShopFindAllProducts();
        $this::assertCount(1, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllProductsIncludesBundlesAndSubscriptions ()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $result = $this->subject->rkwShopFindAllProducts();
        $this::assertCount(3, $result);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllProductsReturnsProductsWithCumulatedStocks ()
    {
        /**
         * Scenario:
         *
         * Given there are three products
         * Given each product has two stocks
         * When I fetch the products
         * Then the stocks of the products are cumulated
         * Then external stocks are ignored
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        $result = $this->subject->rkwShopFindAllProducts();

        $this::assertEquals(275, $result[0]['stock']);
        $this::assertEquals(50, $result[1]['stock']);
        $this::assertEquals(53, $result[2]['stock']);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function findAllProductsReturnsProductsWithCommaSeparatedAdminEmails ()
    {

        /**
         * Scenario:
         *
         * Given there are three products
         * Given each products has several admins
         * When I fetch the products
         * Then the admin mails are returned as comma separated list
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

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
    public function findOrdersByTimestampIgnoresEnableFieldsAndDeleted ()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        $this::assertCount(3, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOrdersByTimestampRespectsStoragePid()
    {
        /**
         * Scenario:
         *
         * Given there are two orders
         * Given one order has a different storage pid
         * When I fetch the order
         * Then only one product is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        $this::assertCount(1, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function findOrdersByTimestampReturnsOrdersWithTstampGreaterThanOrEqualToGivenTimestamp()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp(100);
        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOrdersByTimestampReturnsShippingAddressInParentObject()
    {

        /**
         * Scenario:
         *
         * Given there is a order
         * Given that order has a shipping address
         * When I fetch the order
         * Then the order is returned with the shipping address included in the parent object
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check81.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        $this::assertEquals('Emmentaler Allee 15', $result[0]['address']);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOrdersByTimestampIncludesReferencesToDisabledAndDeletedFeUsers()
    {

        /**
         * Scenario:
         *
         * Test does not work without following TypoScript configuration:
         * module.tx_rkwshop.persistence.permanentProperties {
         *      RKW\RkwSoap\Domain\Model\Order = frontendUser,shippingAddress
         * }
         *
         * Given there are three orders
         * Given that one order belongs to disabled frontend user
         * Given that one order belongs to a deleted frontend user
         * When I fetch the orders
         * Then all three orders are returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        $result = $this->subject->rkwShopFindOrdersByTimestamp();
        self::assertCount(3, $result);

        self::assertGreaterThan(0, $result[0]['frontend_user']);
        self::assertGreaterThan(0, $result[1]['frontend_user']);
        self::assertGreaterThan(0, $result[2]['frontend_user']);


    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     **/
    public function findOrderItemsByOrderIncludesDeletedOrderItems()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        $result = $this->subject->rkwShopFindOrderItemsByOrder(1);
        $this::assertCount(2, $result);
    }


    /**
     * @test
     * @throws \Exception
     **/
    public function findOrderItemsByOrderRespectsStoragePid()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check110.xml');

        $result = $this->subject->rkwShopFindOrderItemsByOrder(1);
        $this::assertCount(1, $result);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function findOrderItemsByOrderKeepsReferencesToDeletedAndHiddenProducts()
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
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check120.xml');

        $result = $this->subject->rkwShopFindOrderItemsByOrder(1);
        $this::assertCount(3, $result);

        $this::assertEquals(1, $result[0]['product']);
        $this::assertEquals(2, $result[1]['product']);
        $this::assertEquals(3, $result[2]['product']);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function findOrderItemsByOrderHasAllRelevantReferences ()
    {
        /**
         * Scenario:
         *
         * Given there is an order
         * Given that order has one order item
         * Given that order item has a reference to a product
         * Given that order item has a reference to an order
         * When I fetch the order-items for that order
         * Then the order item is returned
         * Then the reference to the product is returned
         * Then the reference to the order is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check121.xml');

        $result = $this->subject->rkwShopFindOrderItemsByOrder(1);
        $this::assertCount(1, $result);

        $this::assertEquals(1, $result[0]['product']);
        $this::assertEquals(1, $result[0]['ext_order']);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setOrderedExternalForProductStoresGivenValue ()
    {

        /**
         * Scenario:
         *
         * Given there is a product
         * When I set the external order for that product
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check130.xml');
        self::assertTrue($this->subject->rkwShopSetOrderedExternalForProduct(1, 999));

        /** @var \RKW\RkwSoap\Domain\Model\Product $product */
        $product = $this->productRepository->findByIdentifier(1);
        self::assertEquals(999, $product->getOrderedExternal());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function setOrderedExternalForProductStoresGivenValueIfProductIsDeleted ()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted product
         * When I set the external order for that product
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check131.xml');
        self::assertTrue($this->subject->rkwShopSetOrderedExternalForProduct(1, 999));

        /** @var \RKW\RkwSoap\Domain\Model\Product $product */
        $product = $this->productRepository->findByIdentifier(1);

        self::assertEquals(999, $product->getOrderedExternal());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setOrderedExternalForProductStoresGivenValueIfProductIsHidden ()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted product
         * When I set the external order for that product
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check132.xml');
        self::assertTrue($this->subject->rkwShopSetOrderedExternalForProduct(1, 999));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByIdentifier(1);
        self::assertEquals(999, $product->getOrderedExternal());

    }

    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function setStatusForOrderSetsGivenStatus ()
    {

        /**
         * Scenario:
         *
         * Given there is a order
         * When I set a valid status for that order
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check140.xml');

        self::assertTrue($this->subject->rkwShopSetStatusForOrder(1, 100));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByIdentifier(1);
        self::assertEquals(100, $order->getStatus());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setStatusForOrderChecksForValidStatusCodes ()
    {
        /**
         * Scenario:
         *
         * Given there is a order
         * When I set an invalid status for that order
         * Then false is returned
         * Then the value is not stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check140.xml');

        self::assertFalse($this->subject->rkwShopSetStatusForOrder(1, 99999));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByIdentifier(1);
        self::assertEquals(0, $order->getStatus());
    }


    /**
     * @test
     */
    public function setStatusForOrderChecksForExistingOrders ()
    {
        /**
         * Scenario:
         *
         * Given there no order
         * When I set a valid status for a non-existing order
         * Then false is returned
         */
        self::assertFalse($this->subject->rkwShopSetStatusForOrder(1, 100));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setStatusForOrderSetsGivenStatusIfOrderIsDeleted ()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted order
         * When I set a valid status for that order
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check150.xml');

        self::assertTrue($this->subject->rkwShopSetStatusForOrder(1, 100));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);
        self::assertEquals(100, $order->getStatus());

    }


    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function setDeletedForOrderDeletesGivenOrder ()
    {
        /**
         * Scenario:
         *
         * Given there is an order
         * When I set that order as deleted
         * Then true is returned
         * Then the order is set to deleted
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check160.xml');

        self::assertTrue($this->subject->rkwShopSetDeletedForOrder(1, 1));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);
        self::assertEquals(1, $order->getDeleted());

    }

    /**
     * @test
     */
    public function setDeletedForOrderChecksForExistingOrders ()
    {

        /**
         * Scenario:
         *
         * Given there is no order
         * When I set a set a non-existing order to deleted
         * Then false is returned
         */
        self::assertFalse($this->subject->rkwShopSetDeletedForOrder(1, 1));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setDeletedForOrderChecksForValidValues ()
    {

        /**
         * Scenario:
         *
         * Given there is an order
         * When I set that order as deleted with an invalid value
         * Then false is returned
         * Then the order is not set to deleted
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check160.xml');

        self::assertFalse($this->subject->rkwShopSetDeletedForOrder(1, 5));

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByUid(1);
        self::assertEquals(0, $order->getDeleted());

    }



    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addStockForProductAddsAStock ()
    {
        /**
         * Scenario:
         *
         * Given there is a product
         * When I add a stock to the product
         * Then true is returned
         * Then the stock is added with all given values
         * Then the stock has isExternal set
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check170.xml');

        self::assertTrue($this->subject->rkwShopAddStockForProduct(1, 5, 'Test', 111));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        $stock = $product->getStock()->toArray();

        self::assertCount(2, $stock);

        self::assertEquals(5, $stock[1]->getAmount());
        self::assertEquals('Test', $stock[1]->getComment());
        self::assertEquals(111, $stock[1]->getDeliveryStart());
        self::assertEquals(true, $stock[1]->getIsExternal());


    }

    /**
     * @test
     * @throws \Exception
     */
    public function addStockForProductAddsAStockIfTheProductIsDeleted ()
    {
        /**
         * Scenario:
         *
         * Given there is a product
         * When I add a stock to the product
         * Then true is returned
         * Then the stock is added with all given values
         * Then the stock has isExternal set
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check180.xml');

        self::assertTrue($this->subject->rkwShopAddStockForProduct(1, 5, 'Test', 111));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByIdentifier(1);

        $stock = $product->getStock()->toArray();

        self::assertCount(2, $stock);

        self::assertEquals(5, $stock[1]->getAmount());
        self::assertEquals('Test', $stock[1]->getComment());
        self::assertEquals(111, $stock[1]->getDeliveryStart());
        self::assertEquals(true, $stock[1]->getIsExternal());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function addStockForProductAddsAStockIfTheProductIsHidden ()
    {
        /**
         * Scenario:
         *
         * Given there is a product
         * When I add a stock to the product
         * Then true is returned
         * Then the stock is added with all given values
         * Then the stock has isExternal set
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check190.xml');

        self::assertTrue($this->subject->rkwShopAddStockForProduct(1, 5, 'Test', 111));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);
        $stock = $product->getStock()->toArray();

        self::assertCount(2, $stock);

        self::assertEquals(5, $stock[1]->getAmount());
        self::assertEquals('Test', $stock[1]->getComment());
        self::assertEquals(111, $stock[1]->getDeliveryStart());
        self::assertEquals(true, $stock[1]->getIsExternal());

    }



    //=============================================
    /**
     * @test
     * @throws \Exception
     */
    public function setStatusForOrderItemSetsGivenStatus ()
    {

        /**
         * Scenario:
         *
         * Given there is a order item
         * When I set a valid status for that order item
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');

        self::assertTrue($this->subject->rkwShopSetStatusForOrderItem(1, 100));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = $this->orderItemRepository->findByUid(1);
        self::assertEquals(100, $orderItem->getStatus());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setStatusForOrderItemChecksForValidStatusCodes ()
    {
        /**
         * Scenario:
         *
         * Given there is a order item
         * When I set an invalid status for that order item
         * Then false is returned
         * Then the value is not stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');

        self::assertFalse($this->subject->rkwShopSetStatusForOrderItem(1, 99999));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = $this->orderItemRepository->findByUid(1);
        self::assertEquals(0, $orderItem->getStatus());
    }


    /**
     * @test
     */
    public function setStatusForOrderItemChecksForExistingOrderItems ()
    {
        /**
         * Scenario:
         *
         * Given there no order item
         * When I set a valid status for a non-existing order item
         * Then false is returned
         */
        self::assertFalse($this->subject->rkwShopSetStatusForOrderItem(1, 100));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function setStatusForOrderItemSetsGivenStatusIfOrderItemIsDeleted ()
    {

        /**
         * Scenario:
         *
         * Given there is a deleted order item
         * When I set a valid status for that order item
         * Then true is returned
         * Then the value is stored in the database
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check210.xml');

        self::assertTrue($this->subject->rkwShopSetStatusForOrderItem(1, 100));

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
        $orderItem = $this->orderItemRepository->findByIdentifier(1);
        self::assertEquals(100, $orderItem->getStatus());

    }



    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }








}
