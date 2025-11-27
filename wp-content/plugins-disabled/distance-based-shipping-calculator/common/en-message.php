<?php
/**
 * All App Name messages
 */

namespace EnDistanceBaseShippingMessage;

/**
 * Messages are relate to errors, warnings, headings
 * Class EnDistanceBaseShippingMessage
 * @package EnDistanceBaseShippingMessage
 */
class EnDistanceBaseShippingMessage
{
    /**
     * Add all messages
     * EnDistanceBaseShippingMessage constructor.
     */
    public function __construct()
    {
        define('EN_DBSC_707', "Please verify credentials at connection settings panel.");
        define('EN_DBSC_708', "Please enter valid US or Canada zip code.");
        define('EN_DBSC_709', "Success! The test resulted in a successful connection.");
        define('EN_DBSC_710', "Zip code already exists.");
        define('EN_DBSC_711', "connection settings detail missing.");
        define('EN_DBSC_712', "Shipping parameters are not correct.");
        define('EN_DBSC_713', "Origin address is missing.");
        define('EN_DBSC_714', "");
    }
}