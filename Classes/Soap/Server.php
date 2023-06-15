<?php

namespace RKW\RkwSoap\Soap;

use Madj2k\CoreExtended\Utility\GeneralUtility as Common;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
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
 * Class Server
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Server
{

    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;

    /**
     * frontendUserGroupRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;

    /**
     * shippingAddressRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\ShippingAddressRepository
     */
    protected $shippingAddressRepository;

    /**
     * orderRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\OrderRepository
     */
    protected $orderRepository;

    /**
     * orderItemRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\OrderItemRepository
     */
    protected $orderItemRepository;

    /**
     * productRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\ProductRepository
     */
    protected $productRepository;

    /**
     * stockRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\StockRepository
     */
    protected $stockRepository;

    /**
     * seriesRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\SeriesRepository
     */
    protected $seriesRepository;


    /**
     * eventRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\EventRepository
     */
    protected $eventRepository;


    /**
     * eventPlaceRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\EventPlaceRepository
     */
    protected $eventPlaceRepository;


    /**
     * eventReservationRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\EventReservationRepository
     */
    protected $eventReservationRepository;


    /**
     * eventReservationAddPersonRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\EventReservationAddPersonRepository
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


    /**
     * @var \RKW\RkwSoap\Soap\Subject\FeRegister
     */
    protected $subjectFeRegister;


    /**
     * @var \RKW\RkwSoap\Soap\Subject\RkwEvents
     */
    protected $subjectRkwEvents;


    /**
     * @var \RKW\RkwSoap\Soap\Subject\RkwShop
     */
    protected $subjectRkwShop;


    /**
     * @var string
     */
    protected string $storagePids = '';



    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);

        // load always for basic feUser functionality
        $this->subjectFeRegister = $objectManager->get(\RKW\RkwSoap\Soap\Subject\FeRegister::class);
        if (ExtensionManagementUtility::isLoaded('fe_register')) {
            $this->shippingAddressRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\ShippingAddressRepository::class);
        }

        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            $this->subjectRkwShop = $objectManager->get(\RKW\RkwSoap\Soap\Subject\RkwShop::class);

            $this->orderRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\OrderRepository::class);
            $this->orderItemRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\OrderItemRepository::class);
            $this->productRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\ProductRepository::class);
            $this->stockRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\StockRepository::class);
        }

        if (ExtensionManagementUtility::isLoaded('rkw_events')) {
            $this->subjectRkwEvents = $objectManager->get(\RKW\RkwSoap\Soap\Subject\RkwEvents::class);

            $this->eventRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\EventRepository::class);
            $this->eventPlaceRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\EventPlaceRepository::class);
            $this->eventReservationRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\EventReservationRepository::class);
            $this->eventReservationAddPersonRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\EventReservationAddPersonRepository::class);
        }

        if (ExtensionManagementUtility::isLoaded('rkw_basics')) {
            $this->seriesRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\SeriesRepository::class);
        }

        $this->frontendUserRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\FrontendUserRepository::class);
        $this->frontendUserGroupRepository = $objectManager->get(\RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository::class);
        $this->persistenceManager = $objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
    }


    /**
     * Returns current version
     *
     * @return string
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Core\Package\Exception
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function getVersion(): string
    {
        $settings = $this->getSettings();
        $version = ExtensionManagementUtility::getExtensionVersion('rkw_soap');

        if ($settings['soapServer']['version']) {
            $version = $settings['soapServer']['version'];
        }

        return $version;
    }


    /**
     * Returns current storagePids
     *
     * @return string
     */
    public function getStoragePids(): string
    {
        return $this->storagePids;
    }


    /**
     * Set current storagePids
     *
     * @param string $storagePids
     * @return void
     */
    public function setStoragePids(string $storagePids): void
    {
        //$this->storagePids = $storagePids;

        $this->subjectFeRegister->setStoragePids($storagePids);

        if (ExtensionManagementUtility::isLoaded('rkw_events')) {
            $this->subjectRkwEvents->setStoragePids($storagePids);
        }

        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            $this->subjectRkwShop->setStoragePids($storagePids);
        }
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     * Alias of $this->findFeUsersByTimestamp
     *
     * @param int $timestamp
     * @return array
     * @deprecated since 05-10-2017
     */
    public function findFeUserByTimestamp(int $timestamp): array
    {

        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        return $this->findFeUsersByTimestamp($timestamp);
        
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findFeUsersByTimestamp(int $timestamp): array
    {
        return $this->subjectFeRegister->findFeUsersByTimestamp($timestamp);
    }


    /**
     * Returns a FE-users by uid
     *
     * @param int $uid
     * @return array
     */
    public function findFeUserByUid(int $uid): array
    {
        return $this->subjectFeRegister->findFeUserByUid($uid);
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
    public function findFeUserGroupByTimestamp(int $timestamp, int $serviceOnly = 0): array
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        return $this->findFeUserGroupsByTimestamp($timestamp, $serviceOnly);
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     *
     * @param int $timestamp
     * @param int $serviceOnly
     * @return array
     */
    public function findFeUserGroupsByTimestamp(int $timestamp, int $serviceOnly = 0): array
    {
        return $this->subjectFeRegister->findFeUserGroupsByTimestamp($timestamp, $serviceOnly);
    }


    /**
     * Returns all new orders since $timestamp
     * Alias of $this->findOrdersByTimestamp(
     *
     * @param int $timestamp
     * @return array
     * @deprecated since 05-10-2017
     */
    public function findOrderByTimestamp(int $timestamp): array
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        return $this->findOrdersByTimestamp($timestamp);

    }


    /**
     * Returns all new orders since $timestamp
     *
     * @param int $timestamp
     * @return array
     * @deprecated since 12-08-2019
     */
    public function findOrdersByTimestamp(int $timestamp): array
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            try {

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->orderRepository->findByTimestamp($timestamp);
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
                            $this->getLogger()->log(LogLevel::WARNING, sprintf('Non existing product in orderItem with id = %s referenced.', $orderItem->getUid()));
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
                $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
            }
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return [];
    }


    /**
     * Returns all new orders since $timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function rkwShopFindOrdersByTimestamp(int $timestamp = 0): array
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return $this->subjectRkwShop->findOrdersByTimestamp($timestamp);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return [];
    }



    /**
     * Returns all order items for given order-uid
     *
     * @param int $orderUid
     * @return array
     */
    public function rkwShopFindOrderItemsByOrder(int $orderUid): array
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return $this->subjectRkwShop->findOrderItemsByOrder($orderUid);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return [];
    }


    /**
     * Returns all imported publication pages
     *
     * @return array
     */
    public function rkwShopFindAllProducts(): array
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return $this->subjectRkwShop->findAllProducts();
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return [];
    }


    /**
     * Sets external orders for given product uid
     *
     * @param int $productUid
     * @param int $orderedExternal
     * @return bool
     */
    public function rkwShopSetOrderedExternalForProduct(int $productUid, int $orderedExternal): bool
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return $this->subjectRkwShop->setOrderedExternalForProduct($productUid, $orderedExternal);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
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
    public function rkwShopAddStockForProduct(int $productUid, int $amount, string $comment, int $deliveryStart = 0): bool
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return $this->subjectRkwShop->addStockForProduct($productUid, $amount, $comment, $deliveryStart);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
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
    public function rkwShopSetStatusForOrder(int $orderUid, int $status): bool
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return $this->subjectRkwShop->setStatusForOrder($orderUid, $status);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
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
    public function rkwShopSetDeletedForOrder(int $orderUid, int $deleted): bool
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return $this->subjectRkwShop->setDeletedForOrder($orderUid, $deleted);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
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
    public function rkwShopSetStatusForOrderItem(int $orderItemUid, int $status): bool
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            return $this->subjectRkwShop->setStatusForOrderItem($orderItemUid, $status);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_shop is not installed.');
        }

        return false;
    }



    /**
     * Returns all imported publication pages
     *
     * @return array
     * @deprecated since 08-08-2019
     */
    public function findAllPublications(): array
    {

        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {

            // now we need some mapping in order to make the old stuff work
            $tempResults = $this->rkwShopFindAllProducts();
            $finalResults = [];

            /** @var \RKW\RkwSoap\Domain\Model\Product $result */
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

        return [];

    }


    /**
     * Returns all series
     *
     * @return array
     * @deprecated since 08-08-2019
     */
    public function findAllSeries(): array
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {

            // now we need some mapping in order to make the old stuff work
            $tempResults = $this->rkwShopFindAllProducts();
            $finalResults = [];

            /** @var \RKW\RkwSoap\Domain\Model\Product $result */
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

        return [];
    }


    /**
     * Set the status of an order
     *
     * @param int $uid
     * @param int $status
     * @return int
     * @deprecated since 08-08-2019
     */
    public function setOrderStatus(int $uid, int $status): int
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (ExtensionManagementUtility::isLoaded('rkw_order')) {
            try {

                /** @var \RKW\RkwSoap\Domain\Model\Order $order */
                if ($order = $this->orderRepository->findByUidAll(intval($uid))) {

                    if (!in_array($status, array(1, 0))) {
                        $status = 0;
                    }

                    $order->setStatus(intval($status));

                    $this->orderRepository->update($order);
                    $this->persistenceManager->persistAll();

                    return 1;
                    

                }

            } catch (\Exception $e) {
                $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());

                return 99;
                
            }

        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_order is not installed.');

            return 99;
            
        }

        return 0;
        
    }


    /**
     * Set the delete-value of an order
     *
     * @param int $uid
     * @param int $deleted
     * @return int
     * @deprecated since 08-08-2019
     */
    public function setOrderDeleted(int $uid, int $deleted): int
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
        if (ExtensionManagementUtility::isLoaded('rkw_order')) {
            try {

                /** @var \RKW\RkwSoap\Domain\Model\Order $order */
                if ($order = $this->orderRepository->findByUidAll(intval($uid))) {

                    if (!in_array($deleted, array(1, 0))) {
                        $deleted = 0;
                    }

                    $order->setDeleted(intval($deleted));

                    $this->orderRepository->update($order);
                    $this->persistenceManager->persistAll();

                    return 1;
                    

                }

            } catch (\Exception $e) {
                $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());

                return 99;
                
            }

        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_order is not installed.');

            return 99;
            
        }


        return 0;
        
    }


    /**
     * Returns all existing events by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventsByTimestamp(int $timestamp): array
    {
        if (ExtensionManagementUtility::isLoaded('rkw_events')) {
            return $this->subjectRkwEvents->findEventsByTimestamp($timestamp);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_events is not installed.');
        }

        return [];
    }


    /**
     * Returns all existing eventPlaces by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventPlacesByTimestamp(int $timestamp): array
    {
        if (ExtensionManagementUtility::isLoaded('rkw_events')) {
            return $this->subjectRkwEvents->findEventPlacesByTimestamp($timestamp);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_events is not installed.');
        }

        return [];
    }


    /**
     * Returns all existing eventReservations by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventReservationsByTimestamp(int $timestamp): array
    {
        if (ExtensionManagementUtility::isLoaded('rkw_events')) {
            return $this->subjectRkwEvents->findEventReservationsByTimestamp($timestamp);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_events is not installed.');
        }

        return [];
    }


    /**
     * Returns all existing eventReservationAddPersons by timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findEventReservationAddPersonsByTimestamp(int $timestamp): array
    {
        if (ExtensionManagementUtility::isLoaded('rkw_events')) {
            return $this->subjectRkwEvents->findEventReservationAddPersonsByTimestamp($timestamp);
        } else {
            $this->getLogger()->log(LogLevel::ERROR, 'Extension rkw_events is not installed.');
        }

        return [];
    }




    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
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
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return Common::getTypoScriptConfiguration('rkwsoap', $which);
    }

}

