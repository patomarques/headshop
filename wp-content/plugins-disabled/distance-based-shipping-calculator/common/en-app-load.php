<?php
/**
 * App Name load classes.
 */

namespace EnDistanceBaseShippingLoad;

use EnDBSCShippingRatesPopup\EnDBSCShippingRatesPopup;
use EnDistanceBaseShippingConfig\EnDistanceBaseShippingConfig;
use EnDistanceBaseShippingMessage\EnDistanceBaseShippingMessage;
use EnDistanceBaseShippingOrderWidget\EnDistanceBaseShippingOrderWidget;
use EnDistanceBaseShippingPopupAjax\EnDistanceBaseShippingPopupAjax;
use EnDistanceBaseShippingRates\EnDistanceBaseShippingRates;
use EnDistanceBaseShippingRatesTemplateSettings\EnDistanceBaseShippingRatesTemplateSettings;
use EnDistanceBaseShippingUserGuide\EnDistanceBaseShippingUserGuide;
use EnDistanceBaseShippingDB\EnDistanceBaseShippingDB;
use EnDistanceBaseShippingTestConnection\EnDistanceBaseShippingTestConnection;

/**
 * Load classes.
 * Class EnDistanceBaseShippingLoad
 * @package EnDistanceBaseShippingLoad
 */
class EnDistanceBaseShippingLoad
{
    /**
     * Load classes of App Name plugin
     */
    static public function Load()
    {
        EnDistanceBaseShippingConfig::do_config();
        new \_EnDistanceBaseShippingShippingRates();
        new EnDistanceBaseShippingMessage();

        if (is_admin()) {
            new \EnLoader();
            new EnDistanceBaseShippingDB();
            new EnDistanceBaseShippingTestConnection();
            new EnDistanceBaseShippingOrderWidget();
            new EnDistanceBaseShippingUserGuide();
            new EnDistanceBaseShippingPopupAjax();
            new EnDistanceBaseShippingRatesTemplateSettings();
        }
    }
}


