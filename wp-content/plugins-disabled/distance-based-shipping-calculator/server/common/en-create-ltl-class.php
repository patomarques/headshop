<?php
/**
 * Create class for WooCommerce.
 */

namespace EnDistanceBaseShippingCreateLTLClass;

/**
 * Create class in shipping classes.
 * Class EnDistanceBaseShippingCreateLTLClass
 * @package EnDistanceBaseShippingCreateLTLClass
 */
class EnDistanceBaseShippingCreateLTLClass
{
    public $network_wide = false;
    /**
     * Hook for call.
     * EnDistanceBaseShippingCreateLTLClass constructor.
     */
    public function __construct()
    {
        $this->network_wide = (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF']) && str_contains($_SERVER['PHP_SELF'], 'network/plugins.php')) ? true : false;

        add_filter('en_register_activation_hook', array($this, 'en_create_ltl_class'), 10);
    }

    /**
     * When eniture ltl plugin exist ltl class should be created in Shipping classes tab
     */
    public function en_create_ltl_class()
    {
        global $wpdb;
        if (is_multisite() && $this->network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                wp_insert_term(
                    'LTL Freight', 'product_shipping_class', array(
                        'description' => 'The plugin is triggered to provide an LTL freight quote when the shopping cart contains an item that has a designated shipping class. Shipping class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                        'slug' => 'ltl_freight'
                    )
                );
                restore_current_blog();
            }
        } else {
            wp_insert_term(
                'LTL Freight', 'product_shipping_class', array(
                    'description' => 'The plugin is triggered to provide an LTL freight quote when the shopping cart contains an item that has a designated shipping class. Shipping class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                    'slug' => 'ltl_freight'
                )
            );
        }
    }
}