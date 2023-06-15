<?php

namespace RKW\RkwSoap\Soap\Subject;

use RKW\RkwSoap\Utility\FilteredPropertiesUtility;
use Madj2k\CoreExtended\Utility\GeneralUtility as Common;
use Spipu\Html2Pdf\Debug\Debug;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
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
 * Class RkwShop
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RkwShop extends AbstractSubject
{


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
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected Logger $logger;


    /**
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function __construct()
    {
        parent::__construct();

        $this->buildSoapKeyArray(['rkw_shop']);

        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            $this->orderRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\OrderRepository::class);
            $this->orderItemRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\OrderItemRepository::class);
            $this->productRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\ProductRepository::class);
            $this->stockRepository = $this->objectManager->get(\RKW\RkwSoap\Domain\Repository\StockRepository::class);
        }

        $this->persistenceManager = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
    }


    /**
     * setStoragePidsToRepositories
     *
     * @return void
     * @throws InvalidConfigurationTypeException
     */
    public function setStoragePidsToRepositories(): void
    {
        if (ExtensionManagementUtility::isLoaded('rkw_shop')) {
            $this->orderRepository->setStoragePids($this->getStoragePids());
            $this->orderItemRepository->setStoragePids($this->getStoragePids());
            $this->productRepository->setStoragePids($this->getStoragePids());
            $this->stockRepository->setStoragePids($this->getStoragePids());
        }
    }


    /**
     * Returns all new orders since $timestamp
     *
     * @param int $timestamp
     * @return array
     */
    public function findOrdersByTimestamp(int $timestamp = 0): array
    {

        try {

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->orderRepository->findByTimestamp($timestamp);

            if ($results) {
                $finalResults = FilteredPropertiesUtility::filter($results, $this->soapKeyArray['tx_rkwshop_domain_model_order']);

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
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return [];
    }



    /**
     * Returns all order items for given order-uid
     *
     * @param int $orderUid
     * @return array
     */
    public function findOrderItemsByOrder(int $orderUid): array
    {
        try {

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->orderItemRepository->findByOrderUid($orderUid);

            if ($results) {

                return FilteredPropertiesUtility::filter($results, $this->soapKeyArray['tx_rkwshop_domain_model_orderitem']);
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return [];

    }


    /**
     * Returns all imported publication pages
     *
     * @return array
     */
    public function findAllProducts(): array
    {

        try {

            /** @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $results */
            $results = $this->productRepository->findAll();

            if ($results) {
                return FilteredPropertiesUtility::filter($results, $this->soapKeyArray['tx_rkwshop_domain_model_product']);
            }

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
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
    public function setOrderedExternalForProduct(int $productUid, int $orderedExternal): bool
    {
        try {

            /** @var \RKW\RkwShop\Domain\Model\Product $product */
            if ($product = $this->productRepository->findByUid($productUid)) {
                $product->setOrderedExternal($orderedExternal);
                $this->productRepository->update($product);
                $this->persistenceManager->persistAll();

                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
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
    public function addStockForProduct(int $productUid, int $amount, string $comment, int $deliveryStart = 0): bool
    {
        try {

            /** @var \RKW\RkwSoap\Domain\Model\Product $product */
            if ($product = $this->productRepository->findByUid($productUid)) {

                /** @var \RKW\RkwSoap\Domain\Model\Stock $stock */
                $stock = GeneralUtility::makeInstance(\RKW\RkwSoap\Domain\Model\Stock::class);
                $stock->setAmount($amount);
                $stock->setComment($comment);
                $stock->setDeliveryStart($deliveryStart);
                $stock->setIsExternal(true);

                $this->stockRepository->add($stock);

                $product->addStock($stock);
                $this->productRepository->update($product);
                $this->persistenceManager->persistAll();

                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
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
    public function setStatusForOrder(int $orderUid, int $status): bool
    {
        try {

            $validValues = [0, 90, 100, 200];

            /** @var \RKW\RkwSoap\Domain\Model\Order $order*/
            if ($order = $this->orderRepository->findByUid($orderUid)) {
                if (in_array($status, $validValues)) {
                    $order->setStatus($status);
                    $this->orderRepository->update($order);
                    $this->persistenceManager->persistAll();

                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
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
    public function setDeletedForOrder(int $orderUid, int $deleted): bool
    {
        try {

            $validValues = [0, 1];

            /** @var \RKW\RkwSoap\Domain\Model\Order $order*/
            if ($order = $this->orderRepository->findByUid($orderUid)) {

                if (in_array($deleted, $validValues)) {
                    $order->setDeleted($deleted);
                    $this->orderRepository->update($order);
                    $this->persistenceManager->persistAll();

                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
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
    public function setStatusForOrderItem(int $orderItemUid, int $status): bool
    {
        try {

            $validValues = [0, 90, 100, 200];

            /** @var \RKW\RkwSoap\Domain\Model\OrderItem $orderItem*/
            if ($orderItem = $this->orderItemRepository->findByUid($orderItemUid)) {
                if (in_array($status, $validValues)) {
                    $orderItem->setStatus($status);
                    $this->orderItemRepository->update($orderItem);
                    $this->persistenceManager->persistAll();

                    return true;
                }
            }

            return false;

        } catch (\Exception $e) {
            $this->getLogger()->log(LogLevel::ERROR, $e->getMessage());
        }

        return false;
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

