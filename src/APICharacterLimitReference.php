<?php

namespace Omnipay\AuthorizeNet;

class APICharacterLimitReference
{

    /**
     * Character limit for first name.
     * @var integer
     */
    const L_FIRST_NAME = 50;

    /**
     * Character limit for last name.
     * @var integer
     */
    const L_LAST_NAME = 50;

    /**
     * Character limit for company name.
     * @var integer
     */
    const L_COMPANY = 50;

    /**
     * Character limit for address.
     * @var integer
     */
    const L_ADDRESS = 60;

    /**
     * Character limit for city.
     * @var integer
     */
    const L_CITY = 40;

    /**
     * Character limit for state.
     * @var integer
     */
    const L_STATE = 40;

    /**
     * Character limit for zip code.
     * @var integer
     */
    const L_ZIP = 20;

    /**
     * Character limit for country.
     * @var integer
     */
    const L_COUNTRY = 60;

    /**
     * Character limit for phone number.
     * @var integer
     */
    const L_PHONE = 25;

    /**
     * Mapping of field names to their character limits.
     * @var array
     */
    static protected $fieldNameMap = array(
        "firstName"             => self::L_FIRST_NAME,
        "lastName"              => self::L_LAST_NAME,
        "company"               => self::L_COMPANY,
        "address"               => self::L_ADDRESS,
        "city"                  => self::L_CITY,
        "state"                 => self::L_STATE,
        "zip"                   => self::L_ZIP,
        "country"               => self::L_COUNTRY,
        "phoneNumber"           => self::L_PHONE,
        "x_first_name"          => self::L_FIRST_NAME,
        "x_last_name"           => self::L_LAST_NAME,
        "x_company"             => self::L_COMPANY,
        "x_address"             => self::L_ADDRESS,
        "x_city"                => self::L_CITY,
        "x_state"               => self::L_STATE,
        "x_zip"                 => self::L_ZIP,
        "x_country"             => self::L_COUNTRY,
        "x_phone"               => self::L_PHONE,
        "x_ship_to_first_name"  => self::L_FIRST_NAME,
        "x_ship_to_last_name"   => self::L_LAST_NAME,
        "x_ship_to_company"     => self::L_COMPANY,
        "x_ship_to_address"     => self::L_ADDRESS,
        "x_ship_to_city"        => self::L_CITY,
        "x_ship_to_state"       => self::L_STATE,
        "x_ship_to_zip"         => self::L_ZIP,
        "x_ship_to_country"     => self::L_COUNTRY
    );

    /**
     * Truncate all matching fields in object or array.
     * @param object|array $object
     */
    static public function truncateFields(&$object)
    {
        $fieldKeys = array();
        if (is_object($object)) {
            $fieldKeys = array_keys(
                get_object_vars($object)
            );
        } else if (is_array($object)) {
            $fieldKeys = array_keys($object);
        }
        if (!$fieldKeys) {
            return;
        }
        foreach ($fieldKeys as $key) {
            if (isset(self::$fieldNameMap[$key])) {
                if (is_object($object)) {
                    $object->$key = substr(
                        $object->$key,
                        0,
                        self::$fieldNameMap[$key]
                    );
                } else if (is_array($object)) {
                    $object[$key] = substr(
                        $object[$key],
                        0,
                        self::$fieldNameMap[$key]
                    );
                }
            }
        }
    }

}