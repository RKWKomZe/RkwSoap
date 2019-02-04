<?php

namespace RKW\RkwSoap\Soap;

use \RKW\RkwBasics\Helper\Common;

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
     * frontendUserRepository
     *
     * @var \RKW\RkwRegistration\Domain\Repository\TitleRepository
     */
    protected $titleRepository;

    /**
     * orderRepository
     *
     * @var \RKW\RkwOrder\Domain\Repository\OrderRepository
     */
    protected $orderRepository;

    /**
     * orderRepository
     *
     * @var \RKW\RkwOrder\Domain\Repository\PublicationRepository
     */
    protected $publicationRepository;


    /**
     * pagesRepository
     *
     * @var \RKW\RkwOrder\Domain\Repository\PagesRepository
     */
    protected $pagesRepository;


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
                    'tx_rkwregistration_title',
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
                return $this->toArray($results, $keys);
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
                return $this->toArray($results, $keys);
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
                    'send_series',
                    'subscribe',
                    'gender',
                    'title',
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
                    'publication',

                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->orderRepository->findByTimestamp($timestamp);

                if ($results) {
                    return $this->toArray($results, $keys);
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
     * Returns all imported publication pages
     *
     * @return array
     */
    public function findAllPublications()
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_order')) {

            try {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'title',
                    'subtitle',
                    'stock',
                    'allowSeries',
                    'allowSubscription',
                    'series',
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->publicationRepository->findAll();

                if ($results) {
                    return $this->toArray($results, $keys);
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
     * Returns all series
     *
     * @return array
     */
    public function findAllSeries()
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_basics')) {

            try {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'name',
                    'short_name',
                    'description',
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->seriesRepository->findAll();

                if ($results) {
                    return $this->toArray($results, $keys);
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
                    return $this->toArray($results, $keys);
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
                    return $this->toArray($results, $keys);
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
                    return $this->toArray($results, $keys);
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
     * @return  array
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
                    return $this->toArray($results, $keys);
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
     * Returns all imported publication pages
     *
     * @return array
     */
    public function findAllTitles()
    {

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_registration')) {

            try {

                $keys = array(
                    'uid',
                    'crdate',
                    'tstamp',
                    'hidden',
                    'deleted',
                    'name',
                    'name_long',
                    'is_title_after',
                );

                /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
                $results = $this->titleRepository->findAll();

                if ($results) {
                    return $this->toArray($results, $keys);
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
     * Builds a multidimensional array from the QueryResultInterface
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results The query results
     * @param array                                               $keys The field names
     * @return array
     */
    protected function toArray($results, $keys)
    {

        $finalData = array();

        if ($results instanceof \Countable) {

            foreach ($results as $data) {

                $tempData = array();
                foreach ($keys as $key) {

                    $getter = 'get' . ucFirst(Common::camelize($key));

                    // check if we get an Sub-Repository
                    if ($data->$getter() instanceof \SJBR\StaticInfoTables\Domain\Model\Country) {
                        $tempData[$key] = $data->$getter()->getIsoCodeA2();

                    } else {
                        if ($data->$getter() instanceof \SJBR\StaticInfoTables\Domain\Model\Currency) {
                            $tempData[$key] = $data->$getter()->getIsoCodeA3();


                        } else {
                            if ($data->$getter() instanceof \Countable) {

                                $uidList = array();
                                foreach ($data->$getter() as $item)
                                    $uidList[] = $item->getUid();

                                $tempData[$key] = implode(',', $uidList);

                            } else {
                                if ($data->$getter() instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractEntity) {
                                    $tempData[$key] = $data->$getter()->getUid();


                                } else {
                                    if (is_bool($data->$getter())) {
                                        $tempData[$key] = intval($data->$getter());

                                    } else {
                                        $tempData[$key] = $data->$getter();
                                    }
                                }
                            }
                        }
                    }

                }

                $finalData[] = $tempData;
            }
        }

        return $finalData;
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
        //===
    }


    public function __construct()
    {

        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_registration')) {
            $this->frontendUserRepository = $objectManager->get('RKW\RkwRegistration\Domain\Repository\FrontendUserRepository');
            $this->frontendUserGroupRepository = $objectManager->get('RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository');
            $this->titleRepository = $objectManager->get('RKW\RkwRegistration\Domain\Repository\TitleRepository');

        } else {
            $this->frontendUserRepository = $objectManager->get('RKW\RkwSoap\Domain\Repository\FrontendUserRepository');
            $this->frontendUserGroupRepository = $objectManager->get('RKW\RkwSoap\Domain\Repository\FrontendUserGroupRepository');
        }

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rkw_order')) {
            $this->orderRepository = $objectManager->get('RKW\RkwOrder\Domain\Repository\OrderRepository');
            $this->pagesRepository = $objectManager->get('RKW\RkwOrder\Domain\Repository\PagesRepository');
            $this->publicationRepository = $objectManager->get('RKW\RkwOrder\Domain\Repository\PublicationRepository');
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
}

