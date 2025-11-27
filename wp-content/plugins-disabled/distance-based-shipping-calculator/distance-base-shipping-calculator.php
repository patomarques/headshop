<?php

/**
 * Plugin Name: Distance Based Shipping Calculator
 * Plugin URI: https://eniture.com/products/
 * Description: Dynamically retrieves calculate shipping rates by determining the distance between the shipping origin and destination. Multiply it by a rate per mile and displays the results in the WooCommerce shopping cart.
 * Version: 2.0.27
 * Author: Eniture Technology
 * Author URI: http://eniture.com/
 * Text Domain: eniture-technology
 * License: GPL-2.0-or-later
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

require_once 'vendor/autoload.php';

define('EN_DISTANCE_BASE_SHIPPING_MAIN_DIR', __DIR__);
define('EN_DISTANCE_BASE_SHIPPING_MAIN_FILE', __FILE__);

if (empty(\EnDistanceBaseShippingGuard\EnDistanceBaseShippingGuard::en_check_prerequisites('Distance Shipping Calculator', '5.6', '5.0', '3.0'))) {
    require_once 'en-install.php';
}
