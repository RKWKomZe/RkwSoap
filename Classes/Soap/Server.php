<?php

namespace RKW\RkwSoap\Soap;

use RKW\RkwSoap\Utility\ObjectToArrayUtility;
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
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Server
{

    /**
     * frontendUserRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserRepository|\RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;


    /**
     * frontendUserGroupRepository
     *
     * @var \RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository|\RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     */
    protected $frontendUserGroupRepository;


    /**
     * orderRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    protected $orderRepository;


    /**
     * productRepository
     *
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    protected $productRepository;


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

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_registration')) {
            $this->frontendUserRepository = $objectManager->get('RKW\RkwRegistration\Domain\Repository\FrontendUserRepository');
            $this->frontendUserGroupRepository = $objectManager->get('RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository');
        } else {
            $this->frontendUserRepository = $objectManager->get('RKW\RkwSoap\Domain\Repository\FrontendUserRepository');
            $this->frontendUserGroupRepository = $objectManager->get('RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository');
        }

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_shop')) {
            $this->orderRepository = $objectManager->get('RKW\RkwShop\Domain\Repository\OrderRepository');
            $this->productRepository = $objectManager->get('RKW\RkwShop\Domain\Repository\ProductRepository');
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

        $this->persistenceManager = $objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager');

    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     * Alias of $this->findFeUsersByTimestamp
     *
     * @param integer $timestamp
     * @return array
     * @deprecated since 05-10-2017
     */
    public function findFeUserByTimestamp($timestamp)
    {

        return $this->findFeUsersByTimestamp($timestamp);
        //===
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     *
     * @param integer $timestamp
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

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_registration')) {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'disable',
                    'deleted',
                    'username',
                    'usergroup',
                    'company',
                    'tx_rkwregistration_gender',
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
                    'tx_rkwregistration_facebook_url',
                    'tx_rkwregistration_twitter_url',
                    'tx_rkwregistration_xing_url',

                );
            }

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->frontendUserRepository->findByTimestamp($timestamp);

            if ($results) {
                return ObjectToArrayUtility::toArray($results, $keys);
            }
            //===

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
     * @param integer $timestamp
     * @param integer $serviceOnly
     * @return array
     * @deprecated since 05-10-2017
     */
    public function findFeUserGroupByTimestamp($timestamp, $serviceOnly = 0)
    {

        return $this->findFeUserGroupsByTimestamp($timestamp, $serviceOnly);
        //===
    }


    /**
     * Returns all FE-users that have been updated since $timestamp
     *
     * @param integer $timestamp
     * @param integer $serviceOnly
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

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_registration')) {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'title',
                    'description',
                    'tx_rkwregistration_is_service',
                );
            }

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->frontendUserGroupRepository->findByTimestamp($timestamp, $serviceOnly);

            if ($results) {
                return ObjectToArrayUtility::toArray($results, $keys);
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
     * @param integer $timestamp
     * @return array
     * @deprecated since 05-10-2017
     */
    public function findOrderByTimestamp($timestamp)
    {

        return $this->findOrdersByTimestamp($timestamp);
        //===
    }


    /**
     * Returns all new orders since $timestamp
     *
     * @param integer $timestamp
     * @return array
     */
    public function findOrdersByTimestamp($timestamp)
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_order')) {

            try {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'status',
                    'page_title',
                    'page_subtitle',
                    'series_title',
                    'send_series',
                    'subscribe',
                    'gender',
                    'first_name',
                    'last_name',
                    'company',
                    'address',
                    'zip',
                    'city',
                    'email',
                    'amount',
                    'frontend_user',
                    'pages',

                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->orderRepository->findByTimestamp($timestamp);

                if ($results) {
                    return ObjectToArrayUtility::toArray($results, $keys);
                }
                //===

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_order is not installed.');
        }

        return array();
        //===
    }


    /**
     * Returns all new orders since $timestamp
     *
     * @param integer $timestamp
     * @return array
     */
    public function rkwShopFindOrdersByTimestamp($timestamp)
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
                        'city'
                    ]
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->orderRepository->findByTimestampSoap($timestamp);

                if ($results) {
                    $finalResult = ObjectToArrayUtility::toArray($results, $keys);

                    // add shipping address without subarray
                    $finalResult = array_merge($finalResult, $finalResult['shipping_address']);
                    unset($finalResult['shipping_address']);

                    var_dump($finalResult);
                    return $finalResult;
                }

            } catch (\Exception $e) {
                $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, $e->getMessage());
            }

        } else {
            $this->getLogger()->log(\TYPO3\CMS\Core\Log\LogLevel::ERROR, 'Extension rkw_order is not installed.');
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
                    return ObjectToArrayUtility::toArray($results, $keys);
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
     * @deprecated since 08-08-2019
     */
    public function findAllPublications()
    {

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
     * Returns all existing events by timestamp
     *
     * @param integer $timestamp
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
                    return ObjectToArrayUtility::toArray($results, $keys);
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
     * @param integer $timestamp
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
                    return ObjectToArrayUtility::toArray($results, $keys);
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
     * @param integer $timestamp
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
                    return ObjectToArrayUtility::toArray($results, $keys);
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
     * @param integer $timestamp
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
                    return ObjectToArrayUtility::toArray($results, $keys);
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
     * Set the status of an order
     *
     * @param integer $uid
     * @param integer $status
     * @return integer
     */
    public function setOrderStatus($uid, $status)
    {

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
     * @param integer $uid
     * @param integer $deleted
     * @return integer
     */
    public function setOrderDeleted($uid, $deleted)
    {

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



}

