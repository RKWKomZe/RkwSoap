<?php

namespace RKW\RkwSoap\Soap;

use RKW\RkwSoap\Utility\FilteredPropertiesUtility;
use Madj2k\CoreExtended\Utility\GeneralUtility as Common;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
 * Class Server
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Server
{

    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserRepository|\Madj2k\FeRegister\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * frontendUserGroupRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository|\Madj2k\FeRegister\Domain\Repository\FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * shippingAddressRepository
     *
     * @var \Madj2k\FeRegister\Domain\Repository\ShippingAddressRepository
     */
    protected $shippingAddressRepository;

    /**
     * orderRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    protected $orderRepository;

    /**
     * orderItemRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     */
    protected $orderItemRepository;


    /**
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    protected $productRepository;

    /**
     * stockRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\StockRepository
     */
    protected $stockRepository;

    /**
     * seriesRepository
     *
     * @var \RKW\RkwBasics\Domain\Repository\SeriesRepository
     */
    protected $seriesRepository;


    /**
     * eventRepository
     *
     * @var \RKW\RkwEvents\Domain\Repository\EventRepository
     */
    protected $eventRepository;


    /**
     * eventPlaceRepository
     *
     * @var \RKW\RkwEvents\Domain\Repository\EventPlaceRepository
     */
    protected $eventPlaceRepository;


    /**
     * eventReservationRepository
     *
     * @var \RKW\RkwEvents\Domain\Repository\EventReservationRepository
     */
    protected $eventReservationRepository;


    /**
     * eventReservationAddPersonRepository
     *
     * @var \RKW\RkwEvents\Domain\Repository\EventReservationAddPersonRepository
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

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fe_register')) {
            $this->shippingAddressRepository = $objectManager->get('Madj2k\FeRegister\Domain\Repository\ShippingAddressRepository');
        }

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {
            $this->orderRepository = $objectManager->get('RKW\RkwShop\Domain\Repository\OrderRepository');
            $this->orderItemRepository = $objectManager->get('RKW\RkwShop\Domain\Repository\OrderItemRepository');
            $this->productRepository = $objectManager->get('RKW\RkwShop\Domain\Repository\ProductRepository');
            $this->stockRepository = $objectManager->get('RKW\RkwShop\Domain\Repository\StockRepository');

        }

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_events')) {
            $this->eventRepository = $objectManager->get('RKW\RkwEvents\Domain\Repository\EventRepository');
            $this->eventPlaceRepository = $objectManager->get('RKW\RkwEvents\Domain\Repository\EventPlaceRepository');
            $this->eventReservationRepository = $objectManager->get('RKW\RkwEvents\Domain\Repository\EventReservationRepository');
            $this->eventReservationAddPersonRepository = $objectManager->get('RKW\RkwEvents\Domain\Repository\EventReservationAddPersonRepository');
        }

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_basics')) {
            $this->seriesRepository = $objectManager->get('RKW\RkwBasics\Domain\Repository\SeriesRepository');
        }

        $this->frontendUserRepository = $objectManager->get('RKW\RkwSoap\Domain\Repository\FrontendUserRepository');
        $this->frontendUserGroupRepository = $objectManager->get('RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository');
        $this->persistenceManager = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager');

    }


    /**
     * Returns current version
     *
     * @return string
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Core\Package\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getVersion()
    {
        $settings = $this->getSettings();
        $version = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('rkw_soap');

        if ($settings['soapServer']['version']) {
            $version = $settings['soapServer']['version'];
        }

        return $version;
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     * Alias of $this->findFeUsersByTimestamp
     *
     * @param int $timestamp
     * @return array
     * @deprecated since 05-10-2017
     */
    public function findFeUserByTimestamp($timestamp)
    {

        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        return $this->findFeUsersByTimestamp($timestamp);
        //===
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findFeUsersByTimestamp($timestamp)
    {

        try {

            $keys = array(
                'uid',
                'crdate',
                'tstamp',
                'disable',
                'deleted',
                'username',
                'usergroup',
                'company',
                'first_name',
                'middle_name',
                'last_name',
                'address',
                'zip',
                'city',
                'telephone',
                'fax',
                'email',
                'www',
            );

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fe_register')) {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'disable',
                    'deleted',
                    'username',
                    'usergroup',
                    'company',
                    'tx_feregister_gender',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'address',
                    'zip',
                    'city',
                    'telephone',
                    'fax',
                    'email',
                    'www',
                    'tx_feregister_facebook_url',
                    'tx_feregister_twitter_url',
                    'tx_feregister_xing_url',

                );
            }

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->frontendUserRepository->findByTimestamp($timestamp, false);

            // get basic data from shipping address if nothing is set in account
            if ($this->shippingAddressRepository) {

                /** @var \Madj2k\FeRegister\Domain\Model\FrontendUser $result */
                foreach ($results as $result) {
                    if (
                        (! $result->getFirstName())
                        && (! $result->getLastName())
                    ){

                        /** @var \Madj2k\FeRegister\Domain\Model\ShippingAddress $shippingAddress */
                        if ($shippingAddress = $this->shippingAddressRepository->findOneByFrontendUser ($result->getUid())) {
                            $result->setTxFeregisterGender($shippingAddress->getGender());
                            $result->setFirstName($shippingAddress->getFirstName());
                            $result->setLastName($shippingAddress->getLastName());
                            $result->setAddress($shippingAddress->getAddress());
                            $result->setZip($shippingAddress->getZip());
                            $result->setCity($shippingAddress->getCity());
                            $result->setCompany($shippingAddress->getCompany());
                        }
                    }
                }
            }

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $keys);
            }
            //===

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
        }

        return array();
        //===
    }


    /**
     * Returns a FE-users by uid
     *
     * @param int $uid
     * @return array
     */
    public function findFeUserByUid($uid)
    {

        try {

            $keys = array(
                'uid',
                'crdate',
                'tstamp',
                'disable',
                'deleted',
                'username',
                'usergroup',
                'company',
                'first_name',
                'middle_name',
                'last_name',
                'address',
                'zip',
                'city',
                'telephone',
                'fax',
                'email',
                'www',
            );

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fe_register')) {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'disable',
                    'deleted',
                    'username',
                    'usergroup',
                    'company',
                    'tx_feregister_gender',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'address',
                    'zip',
                    'city',
                    'telephone',
                    'fax',
                    'email',
                    'www',
                    'tx_feregister_facebook_url',
                    'tx_feregister_twitter_url',
                    'tx_feregister_xing_url',

                );
            }


            /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $result */
            $result = $this->frontendUserRepository->findByUid($uid);
            if ($result) {

                // get basic data from shipping address if nothing is set in account
                if ($this->shippingAddressRepository) {

                    /** @var \Madj2k\FeRegister\Domain\Model\FrontendUser $result */
                    if (
                        (! $result->getFirstName())
                        && (! $result->getLastName())
                    ){

                        /** @var \Madj2k\FeRegister\Domain\Model\ShippingAddress $shippingAddress */
                        if ($shippingAddress = $this->shippingAddressRepository->findOneByFrontendUser ($result->getUid())) {
                            $result->setTxFeregisterGender($shippingAddress->getGender());
                            $result->setFirstName($shippingAddress->getFirstName());
                            $result->setLastName($shippingAddress->getLastName());
                            $result->setAddress($shippingAddress->getAddress());
                            $result->setZip($shippingAddress->getZip());
                            $result->setCity($shippingAddress->getCity());
                            $result->setCompany($shippingAddress->getCompany());
                        }
                    }

                }

                return FilteredPropertiesUtility::filter($result, $keys);
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
        }

        return array();
        //===
    }

    /**
     * Returns all FE-users that have been updated since $timestamp
     * Alias of $this->findFeUserGroupsByTimestamp
     *
     * @param int $timestamp
     * @param int $serviceOnly
     * @return array
     * @deprecated since 05-10-2017
     */
    public function findFeUserGroupByTimestamp($timestamp, $serviceOnly = 0)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        return $this->findFeUserGroupsByTimestamp($timestamp, $serviceOnly);
        //===
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     *
     * @param int $timestamp
     * @param int $serviceOnly
     * @return array
     */
    public function findFeUserGroupsByTimestamp($timestamp, $serviceOnly = 0)
    {

        try {

            $keys = array(
                'uid',
                'crdate',
                'tstamp',
                'hidden',
                'deleted',
                'title',
                'description',
            );

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('fe_register')) {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'title',
                    'description',
                    'tx_feregister_is_service',
                );
            }

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->frontendUserGroupRepository->findByTimestamp($timestamp, $serviceOnly);

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $keys);
            }
            //===

        } catch (\Exception $e) {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
        }

        return array();
        //===
    }


    /**
     * Returns all new orders since $timestamp
     * Alias of $this->findOrdersByTimestamp(
     *
     * @param int $timestamp
     * @return array
     * @deprecated since 05-10-2017
     */
    public function findOrderByTimestamp($timestamp)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        return $this->findOrdersByTimestamp($timestamp);
        //===
    }


    /**
     * Returns all new orders since $timestamp
     *
     * @param int $timestamp
     * @return array
     * @deprecated since 12-08-2019
     */
    public function findOrdersByTimestamp($timestamp)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->orderRepository->findByTimestampSoap($timestamp);
                $finalResults = [];

                /** @var \RKW\RkwShop\Domain\Model\Order $order */
                foreach ($results as $order) {

                    // first we need to check for all items in this order
                    // and sort them according to the product-type into three types.
                    // The key is the product uid
                    $orderItemDefaults = [];
                    $orderItemBundles = [];
                    $orderItemSubscriptions = [];

                    /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem */
                    foreach ($order->getOrderItem() as $orderItem) {

                        if ($orderItem->getProduct()) {
                            if ($orderItem->getProduct()->getRecordType() == '\RKW\RkwShop\Domain\Model\ProductSubscription') {
                                $orderItemSubscriptions[$orderItem->getProduct()->getUid()] = $orderItem;

                            } else {
                                if ($orderItem->getProduct()->getRecordType() == '\RKW\RkwShop\Domain\Model\ProductBundle') {
                                    $orderItemBundles[$orderItem->getProduct()->getUid()] = $orderItem;

                                } else {
                                    $orderItemDefaults[$orderItem->getProduct()->getUid()] = $orderItem;

                                }
                            }
                        } else {
                            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::WARNING, sprintf('Non existing product in orderItem with id = %s referenced.', $orderItem->getUid()));
                        }
                    }

                    // based on the default we check for corresponding subscriptions
                    $subscribe = false;
                    $sendSeries = false;
                    $seriesTitle = null;

                    /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItemDefault */
                    $cnt = 0;
                    foreach ($orderItemDefaults as $orderItemDefault) {

                        // has a bundle?
                        /** @var \RKW\RkwShop\Domain\Model\Product $productBundle */
                        if ($productBundle = $orderItemDefault->getProduct()->getProductBundle()) {

                            // is a subscription?
                            if ($productBundle->getRecordType() == '\RKW\RkwShop\Domain\Model\ProductSubscription') {
                                if (isset($orderItemSubscriptions[$productBundle->getUid()])) {
                                    $subscribe = true;
                                }
                            } else {
                                $sendSeries = true;
                            }

                            $seriesTitle = $productBundle->getTitle();
                        }

                        // now build order the old way:
                        $orderOld = [
                            'uid'           => $orderItemDefault->getOrder()->getUid() + $cnt,
                            'crdate'        => $orderItemDefault->getOrder()->getCrdate(),
                            'tstamp'        => $orderItemDefault->getOrder()->getTstamp(),
                            'hidden'        => $orderItemDefault->getOrder()->getHidden(),
                            'deleted'       => $orderItemDefault->getOrder()->getDeleted(),
                            'status'        => $orderItem->getOrder()->getStatus(),
                            'page_title'    => $orderItemDefault->getProduct()->getTitle(),
                            'page_subtitle' => $orderItemDefault->getProduct()->getSubtitle(),
                            'series_title'  => $seriesTitle,
                            'send_series'   => intval($sendSeries),
                            'subscribe'     => intval($subscribe),
                            'gender'        => $orderItemDefault->getOrder()->getShippingAddress()->getGender(),
                            'first_name'    => $orderItemDefault->getOrder()->getShippingAddress()->getFirstName(),
                            'last_name'     => $orderItemDefault->getOrder()->getShippingAddress()->getLastName(),
                            'company'       => $orderItemDefault->getOrder()->getShippingAddress()->getCompany(),
                            'address'       => $orderItemDefault->getOrder()->getShippingAddress()->getAddress(),
                            'zip'           => $orderItemDefault->getOrder()->getShippingAddress()->getZip(),
                            'city'          => $orderItemDefault->getOrder()->getShippingAddress()->getCity(),
                            'email'         => $orderItemDefault->getOrder()->getEmail(),
                            'amount'        => $orderItemDefault->getAmount(),
                            'frontend_user' => ($orderItemDefault->getOrder()->getFrontendUser() ? $orderItemDefault->getOrder()->getFrontendUser()->getUid() : 0),
                            'pages'         => $orderItemDefault->getProduct()->getUid(),
                        ];

                        $finalResults[] = $orderOld;
                        $cnt += 100000;
                    }
                }

                return $finalResults;

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }


        return array();
        //===
    }


    /**
     * Returns all new orders since $timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function rkwShopFindOrdersByTimestamp($timestamp = 0)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                $keys = array(
                    'uid',
                    'pid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'status',

                    'email',
                    'frontend_user',
                    'remark',

                    'shipping_address' => [
                        'gender',
                        'title',
                        'first_name',
                        'last_name',
                        'company',
                        'address',
                        'zip',
                        'city',
                    ],
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->orderRepository->findByTimestampSoap($timestamp);

                if ($results) {
                    $finalResults = FilteredPropertiesUtility::filter($results, $keys);

                    // add shipping address without sub-array
                    foreach ($finalResults as &$finalResult) {
                        if (
                            (isset($finalResult['shipping_address']))
                            && (is_array($finalResult['shipping_address']))
                        ) {
                            $finalResult = array_merge($finalResult, $finalResult['shipping_address']);
                            unset($finalResult['shipping_address']);
                        }
                    }

                    return $finalResults;
                }

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return array();
        //===
    }



    /**
     * Returns all order items for given order-uid
     *
     * @param int $orderUid
     * @return array
     */
    public function rkwShopFindOrderItemsByOrder($orderUid)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                $keys = array(
                    'uid',
                    'pid',
                    'crdate',
                    'tstamp',
                    'deleted',

                    'ext_order',
                    'product',
                    'amount',
                    'is_pre_order',

                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->orderItemRepository->findByOrderUidSoap($orderUid);

                if ($results) {

                    $finalResults = FilteredPropertiesUtility::filter($results, $keys);
                    return $finalResults;
                }

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return array();
        //===
    }


    /**
     * Returns all imported publication pages
     *
     * @return array
     */
    public function rkwShopFindAllProducts()
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                $keys = array(
                    'uid',
                    'pid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'title',
                    'subtitle',
                    'page',
                    'stock',
                    'product_bundle',
                    'allow_single_order',
                    'ordered_external',
                    'backend_user',
                    'record_type',
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->productRepository->findAllSoap();

                if ($results) {
                    return FilteredPropertiesUtility::filter($results, $keys);
                }

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return array();
        //===
    }


    /**
     * Sets external orders for given product uid
     *
     * @param int $productUid
     * @param int $orderedExternal
     * @return bool
     */
    public function rkwShopSetOrderedExternalForProduct($productUid, $orderedExternal)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                /** @var \RKW\RkwShop\Domain\Model\Product $product */
                if ($product = $this->productRepository->findByUidSoap(intval($productUid))) {
                    $product->setOrderedExternal(intval($orderedExternal));
                    $this->productRepository->update($product);
                    $this->persistenceManager->persistAll();

                    return true;
                }

                return false;

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return false;
    }

    /**
     * Adds stock for given product uid
     *
     * @param int $productUid
     * @param int $amount
     * @param string $comment
     * @param int $deliveryStart
     * @return bool
     */
    public function rkwShopAddStockForProduct($productUid, $amount, $comment, $deliveryStart = 0)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                /** @var \RKW\RkwShop\Domain\Model\Product $product */
                if ($product = $this->productRepository->findByUidSoap($productUid)) {

                    /** @var \RKW\RkwShop\Domain\Model\Stock $stock */
                    $stock = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('RKW\RkwShop\Domain\Model\Stock');
                    $stock->setAmount(intval($amount));
                    $stock->setComment($comment);
                    $stock->setDeliveryStart(intval($deliveryStart));
                    $stock->setIsExternal(true);

                    $this->stockRepository->add($stock);

                    $product->addStock($stock);
                    $this->productRepository->update($product);
                    $this->persistenceManager->persistAll();

                    return true;
                }

                return false;

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return false;
    }


    /**
     * Sets status for given order uid
     *
     * @param int $orderUid
     * @param int $status
     * @return bool
     */
    public function rkwShopSetStatusForOrder($orderUid, $status)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                $validValues = [0, 90, 100, 200];

                /** @var \RKW\RkwShop\Domain\Model\Order $order*/
                if ($order = $this->orderRepository->findByUidSoap($orderUid)) {
                    if (in_array($status, $validValues)) {
                        $order->setStatus($status);
                        $this->orderRepository->update($order);
                        $this->persistenceManager->persistAll();

                        return true;
                    }
                }

                return false;

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return false;
    }


    /**
     * Sets deleted for given order uid
     *
     * @param int $orderUid
     * @param int $deleted
     * @return bool
     */
    public function rkwShopSetDeletedForOrder($orderUid, $deleted)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                $validValues = [0, 1];

                /** @var \RKW\RkwShop\Domain\Model\Order $order*/
                if ($order = $this->orderRepository->findByUidSoap($orderUid)) {

                    if (in_array($deleted, $validValues)) {
                        $order->setDeleted($deleted);
                        $this->orderRepository->update($order);
                        $this->persistenceManager->persistAll();

                        return true;
                    }
                }

                return false;

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return false;
    }


    /**
     * Sets status for given orderItem uid
     *
     * @param int $orderItemUid
     * @param int $status
     * @return bool
     */
    public function rkwShopSetStatusForOrderItem($orderItemUid, $status)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            try {

                $validValues = [0, 90, 100, 200];

                /** @var \RKW\RkwShop\Domain\Model\OrderItem $orderItem*/
                if ($orderItem = $this->orderItemRepository->findByUidSoap($orderItemUid)) {
                    if (in_array($status, $validValues)) {
                        $orderItem->setStatus($status);
                        $this->orderItemRepository->update($orderItem);
                        $this->persistenceManager->persistAll();

                        return true;
                    }
                }

                return false;

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return false;
    }



    /**
     * Returns all imported publication pages
     *
     * @return array
     * @deprecated since 08-08-2019
     */
    public function findAllPublications()
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            // now we need some mapping in order to make the old stuff work
            $tempResults = $this->rkwShopFindAllProducts();
            $finalResults = [];

            /** @var \RKW\RkwShop\Domain\Model\Product $result */
            foreach ($tempResults as $result) {

                // do not include what was formerly called "series"
                if (
                    ($result['record_type'] != '\RKW\RkwShop\Domain\Model\ProductBundle')
                    && ($result['record_type'] != '\RKW\RkwShop\Domain\Model\ProductSubscription')
                ) {
                    $result['tx_rkwbasics_series'] = $result['product_bundle'];

                    unset($result['pid']);
                    unset($result['page']);
                    unset($result['stock']);
                    unset($result['product_bundle']);
                    unset($result['allow_single_order']);
                    unset($result['ordered_external']);
                    unset($result['backend_user']);
                    unset($result['record_type']);

                    $finalResults[] = $result;
                }
            }

            return $finalResults;
        }

        return array();
        //===
    }


    /**
     * Returns all series
     *
     * @return array
     * @deprecated since 08-08-2019
     */
    public function findAllSeries()
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {

            // now we need some mapping in order to make the old stuff work
            $tempResults = $this->rkwShopFindAllProducts();
            $finalResults = [];

            /** @var \RKW\RkwShop\Domain\Model\Product $result */
            foreach ($tempResults as $result) {

                // do not include what was formerly called "series"
                if (
                    ($result['record_type'] == '\RKW\RkwShop\Domain\Model\ProductBundle')
                    || ($result['record_type'] == '\RKW\RkwShop\Domain\Model\ProductSubscription')
                ) {
                    $result['name'] = $result['title'];
                    $result['short_name'] = $result['subtitle'];
                    $result['description'] = '';

                    unset($result['pid']);
                    unset($result['title']);
                    unset($result['subtitle']);
                    unset($result['page']);
                    unset($result['stock']);
                    unset($result['product_bundle']);
                    unset($result['allow_single_order']);
                    unset($result['ordered_external']);
                    unset($result['backend_user']);
                    unset($result['record_type']);

                    $finalResults[] = $result;
                }
            }

            return $finalResults;
        }

        return array();
    }


    /**
     * Set the status of an order
     *
     * @param int $uid
     * @param int $status
     * @return int
     * @deprecated since 08-08-2019
     */
    public function setOrderStatus($uid, $status)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_order')) {
            try {

                /** @var \RKW\RkwOrder\Domain\Model\Order $order */
                if ($order = $this->orderRepository->findByUidAll(intval($uid))) {

                    if (!in_array($status, array(1, 0))) {
                        $status = 0;
                    }

                    $order->setStatus(intval($status));

                    $this->orderRepository->update($order);
                    $this->persistenceManager->persistAll();

                    return 1;
                    //===

                }

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());

                return 99;
                //===
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_order is not installed.');

            return 99;
            //===
        }

        return 0;
        //===
    }


    /**
     * Set the delete-value of an order
     *
     * @param int $uid
     * @param int $deleted
     * @return int
     * @deprecated since 08-08-2019
     */
    public function setOrderDeleted($uid, $deleted)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_order')) {
            try {

                /** @var \RKW\RkwOrder\Domain\Model\Order $order */
                if ($order = $this->orderRepository->findByUidAll(intval($uid))) {

                    if (!in_array($deleted, array(1, 0))) {
                        $deleted = 0;
                    }

                    $order->setDeleted(intval($deleted));

                    $this->orderRepository->update($order);
                    $this->persistenceManager->persistAll();

                    return 1;
                    //===

                }

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());

                return 99;
                //===
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_order is not installed.');

            return 99;
            //===
        }


        return 0;
        //===
    }


    /**
     * Returns all existing events by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventsByTimestamp($timestamp)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_events')) {

            try {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'title',
                    'subtitle',
                    'description',
                    'start',
                    'end',
                    'seats',
                    'costs_reg',
                    'costs_red',
                    'costs_red_condition',
                    'costs_tax',
                    'currency',
                    'reg_required',
                    'reg_end',
                    'ext_reg_link',
                    'online_event',
                    'place',
                    'organizer',
                    'reminder_mail_tstamp',
                    'poll_mail_tstamp',
                    'reservation',
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->eventRepository->findByTimestamp($timestamp);

                if ($results) {
                    return FilteredPropertiesUtility::filter($results, $keys);
                }
                //===

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_events is not installed.');
        }

        return array();
        //===
    }

    /**
     * Returns all existing eventPlaces by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventPlacesByTimestamp($timestamp)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_events')) {

            try {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'name',
                    'short',
                    'address',
                    'zip',
                    'city',
                    'country',
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->eventPlaceRepository->findByTimestamp($timestamp);

                if ($results) {
                    return FilteredPropertiesUtility::filter($results, $keys);
                }
                //===

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_events is not installed.');
        }

        return array();
        //===
    }


    /**
     * Returns all existing eventReservations by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventReservationsByTimestamp($timestamp)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_events')) {

            try {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'deleted',
                    'fe_user',
                    'salutation',
                    'first_name',
                    'last_name',
                    'company',
                    'address',
                    'zip',
                    'city',
                    'phone',
                    'fax',
                    'email',
                    'remark',
                    'add_person',
                    'event',
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->eventReservationRepository->findByTimestamp($timestamp);

                if ($results) {
                    return FilteredPropertiesUtility::filter($results, $keys);
                }
                //===

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_events is not installed.');
        }

        return array();
        //===
    }


    /**
     * Returns all existing eventReservationAddPersons by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventReservationAddPersonsByTimestamp($timestamp)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_events')) {

            try {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'deleted',
                    'salutation',
                    'first_name',
                    'last_name',
                    'event_reservation',
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->eventReservationAddPersonRepository->findByTimestamp($timestamp);

                if ($results) {
                    return FilteredPropertiesUtility::filter($results, $keys);
                }
                //===

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_events is not installed.');
        }

        return array();
        //===
    }




    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
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
    protected function getSettings($which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS)
    {
        return Common::getTypoScriptConfiguration('rkwsoap', $which);
    }




}

