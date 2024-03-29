<?php

namespace RKW\RkwSoap\Utility;
use Madj2k\CoreExtended\Utility\GeneralUtility as Common;
use Spipu\Html2Pdf\Debug\Debug;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
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
 * ObjectToFilteredArrayUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwSoap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FilteredPropertiesUtility
{

    /**
     * Builds a multidimensional array from the objects in QueryResultInterface or an array of objects
     *
     * @param mixed $results The query results
     * @param array $keys The field names
     * @return array
     */
    public static function filter($results, array $keys): array
    {
        $resultReturnArray = [];
        if (is_iterable($results)) {

            foreach ($results as $key => $objectOrArray) {
                if ($objectOrArray instanceof AbstractEntity) {
                    $resultReturnArray[] = self::getPropertiesFromObject($objectOrArray, $keys);
                } else if (is_array($objectOrArray)) {
                    $resultReturnArray[] = self::getPropertiesFromArray($objectOrArray, $keys);
                }
            }
        }

        if ($results instanceof AbstractEntity) {
            $resultReturnArray = self::getPropertiesFromObject($results, $keys);
        }

        return $resultReturnArray;
    }


    /**
     * Returns the relevant values depending on the object given
     *
     * @param array $dataArray
     * @param array $propertyArray
     * @return array
     */
    protected static function getPropertiesFromArray(array $dataArray, array $propertyArray): array
    {
        $result = [];
        foreach ($propertyArray as $property => $subProperties) {

            // if there are no sub-properties to fetch
            if (
                (is_numeric($property))
                && (! is_array($subProperties))
            ){
                $property = $subProperties;
                unset($subProperties);
            }

            $result[$property] = self::getPropertyFromArray($dataArray, $property, $subProperties);
        }

        return $result;
    }


    /**
     * Returns the relevant values depending on the object given
     *
     * @param AbstractEntity $object
     * @param array          $propertyArray
     * @return array
     */
    protected static function getPropertiesFromObject(AbstractEntity $object, array $propertyArray): array
    {
        $result = [];
        foreach ($propertyArray as $property => $subProperties) {

            // if there are no sub-properties to fetch
            // @toDo by MF: Check is the addition "is_string" (we have associative arrays now) is really correct. PhpUnit tests are fine..
            if (
                (is_numeric($property) || is_string($property))
                && (! is_array($subProperties))
            ){
                $property = $subProperties;
                unset($subProperties);
            }
            $result[$property] = self::getPropertyFromObject($object, $property, $subProperties ?? []);
        }

        return $result;
    }


    /**
     * Returns the properties of the objects of an object storage
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage
     * @param array                                        $subProperties
     * @return mixed
     */
    protected static function getPropertiesFromObjectStorage(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $objectStorage, array $subProperties = [])
    {

        $result = [];
        $type = null;
        foreach ($objectStorage as $object) {

            // get type of object storage based on first object
            if (!$type) {
                $type ='\\'. get_class($object);
            }

            // now that we know the type, we only allow those classes here
            if ($object instanceof $type) {

                if ($object instanceof AbstractEntity) {

                    if (! empty($subProperties)) {
                        $result[] = self::getPropertiesFromObject($object, $subProperties);
                    } else {
                        $result[] = self::getDefaultPropertyFromObject($object);
                    }
                }
            }
        }

        // build final value
        if (empty($subProperties)) {

            // special handling for stock
            if ($type == '\RKW\RkwShop\Domain\Model\Stock') {
                return array_sum($result);
            }

            return implode(',', $result);
        }

        return $result;
    }


    /**
     * Returns the relevant values depending on the object given
     *
     * @param array  $dataArray
     * @param string $property
     * @param array  $subProperties
     * @return mixed
     */
    protected static function getPropertyFromArray(array $dataArray, string $property, array $subProperties = [])
    {

        if (isset($dataArray[$property])) {

            if (is_array($dataArray[$property])) {

                self::getPropertiesFromArray($dataArray[$property], $subProperties);

            } else {

                 if (is_bool($dataArray[$property])) {
                    return intval($dataArray[$property]);

                } else {
                    return $dataArray[$property];
                }
            }
        }

        return null;
    }

    /**
     * Returns the relevant values depending on the object given
     *
     * @param AbstractEntity $object
     * @param string         $property
     * @param array          $subProperties
     * @return mixed
     */
    protected static function getPropertyFromObject(AbstractEntity $object, string $property, array $subProperties = [])
    {

        if (strpos($property, 'ext_') === 0) {
            $property = substr($property, 4);
        }
        $getter = 'get' . ucFirst(Common::camelize($property));
        if (method_exists($object, $getter)) {

            // is it an object-storage?
            if ($object->$getter() instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {

                if (count($object->$getter()) > 0) {
                    return self::getPropertiesFromObjectStorage($object->$getter(), $subProperties);
                } else {
                    return 0;
                }

            // is it an object or array?
            } else if ($object->$getter() instanceof AbstractEntity) {

                // if we have sub-properties we go and get them as array
                if (! empty($subProperties)) {
                    return self::getPropertiesFromObject($object->$getter(), $subProperties);

                // otherwise we return a single value
                } else {
                    return self::getDefaultPropertyFromObject($object->$getter());
                }

            } else if (is_bool($object->$getter())) {
                return intval($object->$getter());

            } else {
                return $object->$getter();
            }
        }

        return null;
    }


    /**
     * Returns the default property
     *
     * @param AbstractEntity $object
     * @return mixed
     */
    protected static function getDefaultPropertyFromObject(AbstractEntity $object)
    {

        // check for special objects
        if ($object instanceof \SJBR\StaticInfoTables\Domain\Model\Country) {
            return $object->getIsoCodeA2();

        } else if ($object instanceof \SJBR\StaticInfoTables\Domain\Model\Currency) {
            return $object->getIsoCodeA3();

        } else if ($object instanceof \TYPO3\CMS\Extbase\Domain\Model\BackendUser) {
            return $object->getEmail();

        } else if ($object instanceof \RKW\RkwShop\Domain\Model\Stock) {
            if (! $object->getIsExternal()) {
                return $object->getAmount();
            } else {
                return 0;
            }
        }

        // default
        return $object->getUid();
    }

}
