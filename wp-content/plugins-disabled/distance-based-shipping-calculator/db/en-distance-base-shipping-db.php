<?php

/**
 * Handle table.
 */

namespace EnDistanceBaseShippingDB;

/**
 * Generic class to handle profile data.
 * Class EnDistanceBaseShippingDB
 * @package EnDistanceBaseShippingDB
 */
class EnDistanceBaseShippingDB
{
     public $network_wide = false;
    /**
     * Hook for call.
     * EnDistanceBaseShippingDB constructor.
     */
    public function __construct()
    {
        $this->network_wide = (isset($_SERVER['PHP_SELF']) && !empty($_SERVER['PHP_SELF']) && str_contains($_SERVER['PHP_SELF'], 'network/plugins.php')) ? true : false;
        add_filter('en_register_activation_hook', array($this, 'create_profiles_table'), 10, 1);
        add_filter('en_register_activation_hook', array($this, 'create_en_shipping_origin_table'), 10, 1);
        add_filter('en_register_activation_hook', array($this, 'create_en_origin_zones_table'), 10, 1);
        add_filter('en_register_activation_hook', array($this, 'create_en_dbsc_rates_table'), 10, 1);
        add_filter('en_register_activation_hook', array($this, 'create_en_zone_rates_table'), 10, 1);
        add_filter('en_register_activation_hook', array($this, 'en_add_dbsc_shipping_method'), 10, 1);
        add_action('admin_init', array($this, 'create_en_dbsc_rates_table'));
    }

    // Add dbsc shipping method
    function en_add_dbsc_shipping_method()
    {
        $eniture_plugins = get_option('EN_Plugins');
        if (!$eniture_plugins) {
            add_option('EN_Plugins', json_encode(array('distance_base_shipping')));
        } else {
            $plugins_array = json_decode($eniture_plugins, true);
            if (!in_array('distance_base_shipping', $plugins_array)) {
                array_push($plugins_array, 'distance_base_shipping');
                update_option('EN_Plugins', json_encode($plugins_array));
            }
        }
    }

    /**
     * Create table for profiles
     */
    public function create_profiles_table()
    {
        if (is_multisite() && $this->network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;

                $en_charset_collate = $wpdb->get_charset_collate();
                $en_table_name = $wpdb->prefix . 'en_profiles';

                $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
                    id int(11) NOT NULL AUTO_INCREMENT,
                    profile_nickname varchar(50) NOT NULL,
                    profile_define_by varchar(20) DEFAULT "shipping_classes",
                    PRIMARY KEY  (id)        
                )' . $en_charset_collate;

                $wpdb->query($en_created_table);
                $success = empty($wpdb->last_error);
                // to add profile_define_by column if not present
                $profile_define_by_column = $wpdb->get_row("SHOW COLUMNS FROM $en_table_name LIKE 'profile_define_by'");
                if (!(isset($profile_define_by_column->Field) && $profile_define_by_column->Field == 'profile_define_by')) {
                    $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN profile_define_by VARCHAR(20) DEFAULT 'shipping_classes'", $en_table_name));
                }
                $profile_id = null;
                $en_existance_4_table = $wpdb->get_row(sprintf("SELECT `profile_nickname` FROM %s LIMIT 1", $en_table_name));
                if (!isset($en_existance_4_table->profile_nickname)) {
                    $profile_id = $wpdb->insert($en_table_name, ['profile_nickname' => 'General Profile', 'profile_define_by' => 'shipping_classes']);
                }

                $this->create_profile_classes_table($profile_id);
                restore_current_blog();
            }
        } else {
            global $wpdb;

            $en_charset_collate = $wpdb->get_charset_collate();
            $en_table_name = $wpdb->prefix . 'en_profiles';

            $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
                    id int(11) NOT NULL AUTO_INCREMENT,
                    profile_nickname varchar(50) NOT NULL,
                    profile_define_by varchar(20) DEFAULT "shipping_classes",
                    PRIMARY KEY  (id)        
                )' . $en_charset_collate;

            $wpdb->query($en_created_table);
            $success = empty($wpdb->last_error);
            // to add profile_define_by column if not present
            $profile_define_by_column = $wpdb->get_row("SHOW COLUMNS FROM $en_table_name LIKE 'profile_define_by'");
            if (!(isset($profile_define_by_column->Field) && $profile_define_by_column->Field == 'profile_define_by')) {
                $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN profile_define_by VARCHAR(20) DEFAULT 'shipping_classes'", $en_table_name));
            }
            
            $profile_id = null;
            $en_existance_4_table = $wpdb->get_row(sprintf("SELECT `profile_nickname` FROM %s LIMIT 1", $en_table_name));
            if (!isset($en_existance_4_table->profile_nickname)) {
                $profile_id = $wpdb->insert($en_table_name, ['profile_nickname' => 'General Profile', 'profile_define_by' => 'shipping_classes']);
            }

            $this->create_profile_classes_table($profile_id);

        }
    }

    /**
     * Create table for profiles
     */
    public function create_profile_classes_table($profile_id = null)
    {
        if (is_multisite() && $this->network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;
                $en_charset_collate = $wpdb->get_charset_collate();
                $en_table_name = $wpdb->prefix . 'en_profile_classes';

                $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
                id int(11) NOT NULL AUTO_INCREMENT,
                profile_id int(11) NULL,
                shipping_classes int(11) NULL,
                PRIMARY KEY  (id)        
            )' . $en_charset_collate;

                $wpdb->query($en_created_table);
                $success = empty($wpdb->last_error);

                $en_existance_4_table = $wpdb->get_row(sprintf("SELECT `shipping_classes` FROM %s LIMIT 1", $en_table_name));
                if (!isset($en_existance_4_table->shipping_classes)) {
                    $wpdb->insert($en_table_name, ['profile_id' => $profile_id, 'shipping_classes' => '-1']);
                }
                restore_current_blog();
            }
        } else {
            global $wpdb;
            $en_charset_collate = $wpdb->get_charset_collate();
            $en_table_name = $wpdb->prefix . 'en_profile_classes';

            $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
                id int(11) NOT NULL AUTO_INCREMENT,
                profile_id int(11) NULL,
                shipping_classes int(11) NULL,
                PRIMARY KEY  (id)        
            )' . $en_charset_collate;

            $wpdb->query($en_created_table);
            $success = empty($wpdb->last_error);

            $en_existance_4_table = $wpdb->get_row(sprintf("SELECT `shipping_classes` FROM %s LIMIT 1", $en_table_name));
            if (!isset($en_existance_4_table->shipping_classes)) {
                $wpdb->insert($en_table_name, ['profile_id' => $profile_id, 'shipping_classes' => '-1']);
            }

        }
    }

    /**
     * Create table for shipping origins
     */
    public function create_en_shipping_origin_table()
    {
        if (is_multisite() && $this->network_wide ) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;
                $en_charset_collate = $wpdb->get_charset_collate();
                $en_table_name = $wpdb->prefix . 'en_shipping_origins';
                $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
            id int(11) NOT NULL AUTO_INCREMENT,
            profile_id int(11) NULL,
            nickname varchar(150) NOT NULL,
            street_address varchar(255) NOT NULL,
            city varchar(50) NOT NULL,
            state varchar(50) NOT NULL,
            postal_code varchar(20) NOT NULL,
            country_code varchar(50) NOT NULL,
            availability varchar(50) NOT NULL,
            origin_order int(11) NULL,
            PRIMARY KEY  (id)        
        )' . $en_charset_collate;

                $wpdb->query($en_created_table);
                $success = empty($wpdb->last_error);

                // Alter table with adding the column `origin_order`
                $origin_order = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'origin_order'");
                $db_field = isset($origin_order->Field) ? $origin_order->Field : '';
                if ($db_field != 'origin_order') {
                    $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_order int(11) NULL", $en_table_name));
                }

                // Addition of functionality for compatability of old version of plugins
                $en_profiles_table_name = $wpdb->prefix . 'en_profiles';
                $en_shipping_origins = $wpdb->prefix . 'en_shipping_origins';
                $en_profiles_query = "SELECT id FROM $en_profiles_table_name";
                $en_profiles_data = $wpdb->get_results($en_profiles_query);

                foreach ($en_profiles_data as $en_profile_key => $en_profile_data) {
                    $en_profile_id = (isset($en_profile_data->id)) ? $en_profile_data->id : '';
                    $en_profiles_origins_query = "SELECT id,origin_order FROM $en_shipping_origins WHERE profile_id = $en_profile_id";
                    $en_profiles_origins_data = $wpdb->get_results($en_profiles_origins_query);

                    $origin_order = 1;
                    foreach ($en_profiles_origins_data as $en_origins_key => $en_origins_data) {
                        $en_origin_id = (isset($en_origins_data->id)) ? $en_origins_data->id : '';
                        $origin_order_num = (isset($en_origins_data->origin_order)) ? $en_origins_data->origin_order : '';
                        if (empty($origin_order_num) || $origin_order_num == '' || is_null($origin_order_num)) {
                            $wpdb->query("UPDATE $en_shipping_origins SET origin_order=$origin_order WHERE id = $en_origin_id");
                            $origin_order++;
                        }
                    }
                }
                restore_current_blog();

            }
        } else {
            global $wpdb;

            $en_charset_collate = $wpdb->get_charset_collate();
            $en_table_name = $wpdb->prefix . 'en_shipping_origins';
            $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
            id int(11) NOT NULL AUTO_INCREMENT,
            profile_id int(11) NULL,
            nickname varchar(150) NOT NULL,
            street_address varchar(255) NOT NULL,
            city varchar(50) NOT NULL,
            state varchar(50) NOT NULL,
            postal_code varchar(20) NOT NULL,
            country_code varchar(50) NOT NULL,
            availability varchar(50) NOT NULL,
            origin_order int(11) NULL,
            PRIMARY KEY  (id)        
        )' . $en_charset_collate;

            $wpdb->query($en_created_table);
            $success = empty($wpdb->last_error);

            // Alter table with adding the column `origin_order`
            $origin_order = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'origin_order'");
            $db_field = isset($origin_order->Field) ? $origin_order->Field : '';
            if ($db_field != 'origin_order') {
                $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_order int(11) NULL", $en_table_name));
            }

            // Addition of functionality for compatability of old version of plugins
            $en_profiles_table_name = $wpdb->prefix . 'en_profiles';
            $en_shipping_origins = $wpdb->prefix . 'en_shipping_origins';
            $en_profiles_query = "SELECT id FROM $en_profiles_table_name";
            $en_profiles_data = $wpdb->get_results($en_profiles_query);

            foreach ($en_profiles_data as $en_profile_key => $en_profile_data) {
                $en_profile_id = (isset($en_profile_data->id)) ? $en_profile_data->id : '';
                $en_profiles_origins_query = "SELECT id,origin_order FROM $en_shipping_origins WHERE profile_id = $en_profile_id";
                $en_profiles_origins_data = $wpdb->get_results($en_profiles_origins_query);

                $origin_order = 1;
                foreach ($en_profiles_origins_data as $en_origins_key => $en_origins_data) {
                    $en_origin_id = (isset($en_origins_data->id)) ? $en_origins_data->id : '';
                    $origin_order_num = (isset($en_origins_data->origin_order)) ? $en_origins_data->origin_order : '';
                    if (empty($origin_order_num) || $origin_order_num == '' || is_null($origin_order_num)) {
                        $wpdb->query("UPDATE $en_shipping_origins SET origin_order=$origin_order WHERE id = $en_origin_id");
                        $origin_order++;
                    }
                }
            }
        }
    }

    /**
     * Create table for shipping origins relationship to zones
     */
    public function create_en_origin_zones_table()
    {
        if (is_multisite() && $this->network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;

                $en_charset_collate = $wpdb->get_charset_collate();
                $en_table_name = $wpdb->prefix . 'en_origin_zones';

                $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
            id int(11) NOT NULL AUTO_INCREMENT,
            en_origin_id int(11) NOT NULL,
            zone_id int(11) NOT NULL,
            PRIMARY KEY  (id)        
        )' . $en_charset_collate;

                $wpdb->query($en_created_table);
                $success = empty($wpdb->last_error);
                restore_current_blog();
            }
        } else {
            global $wpdb;

            $en_charset_collate = $wpdb->get_charset_collate();
            $en_table_name = $wpdb->prefix . 'en_origin_zones';

            $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
            id int(11) NOT NULL AUTO_INCREMENT,
            en_origin_id int(11) NOT NULL,
            zone_id int(11) NOT NULL,
            PRIMARY KEY  (id)        
        )' . $en_charset_collate;

            $wpdb->query($en_created_table);
            $success = empty($wpdb->last_error);
        }
    }

    /**
     * Create table for shipping rates
     */
    public function create_en_dbsc_rates_table()
    {
        if (is_multisite() && $this->network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;
                $this->create_rates_table_for_dbsc($wpdb);
                restore_current_blog();
            }
        }else {
            global $wpdb;
            $this->create_rates_table_for_dbsc($wpdb);
        }
    }

    public function create_rates_table_for_dbsc($wpdb)
    {
        $en_charset_collate = $wpdb->get_charset_collate();
        $en_table_name = $wpdb->prefix . 'en_dbsc_rates';
        $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
                          id int(11) NOT NULL AUTO_INCREMENT,
                          display_as varchar(264) NOT NULL,
                          description text NOT NULL,
                          rate decimal(6,2) NOT NULL,
                          unit enum("mi","km") NOT NULL,
                          measured_by enum("route","straightline") NOT NULL,
                          min_weight float,
                          max_weight float,
                          rate_condition enum("and","or") NOT NULL,
                          min_length float,
                          max_length float,
                          min_distance float,
                          max_distance float,
                          min_quote float,
                          max_quote float,
                          min_cart_value float,
                          max_cart_value float,
                          cart_value_type enum("total","profile") NOT NULL,
                          calculate_for enum("item","origin","flat","item_after_quotes") NOT NULL,
                          distance_adjustment varchar(20),
                          rate_adjustment varchar(20),
                          display_preference enum("no","description","distance"),
                          address_type enum("both","commercial","residential") DEFAULT "both",
                          default_address_type enum("residential","commercial") DEFAULT "commercial",
                          base_amount decimal(6,2) DEFAULT 0.00,
                          PRIMARY KEY  (id)        
                    )' . $en_charset_collate;

        $wpdb->query($en_created_table);
        $success = empty($wpdb->last_error);

        $query_min_distance = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'min_distance'");
        if (!(isset($query_min_distance->Field) && $query_min_distance->Field == 'min_distance')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN min_distance float ", $en_table_name));
        }

        $query_max_distance = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'max_distance'");
        if (!(isset($query_max_distance->Field) && $query_max_distance->Field == 'max_distance')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN max_distance float ", $en_table_name));
        }

        $query_calculate_for = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'calculate_for'");
        if (!empty($query_calculate_for->Field) && is_string($query_calculate_for->Field) && strpos($query_calculate_for->Field, 'item_after_quotes') === false) {
            $wpdb->query(sprintf('ALTER TABLE %s MODIFY COLUMN calculate_for enum("item","origin","flat","item_after_quotes") NOT NULL ', $en_table_name));
        }

        $query_rate = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'rate'");
        if (!empty($query_rate->Field) && is_string($query_rate->Field) && trim($query_rate->Type) === 'decimal(4,2)') {
            $wpdb->query(sprintf('ALTER TABLE %s MODIFY COLUMN rate decimal(6,2) NOT NULL ', $en_table_name));
        }

        $query_distance_adjustment = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'distance_adjustment'");
        if (!(isset($query_distance_adjustment->Field) && $query_distance_adjustment->Field == 'distance_adjustment')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN distance_adjustment varchar(20) ", $en_table_name));
        }

        $query_rate_adjustment = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'rate_adjustment'");
        if (!(isset($query_rate_adjustment->Field) && $query_rate_adjustment->Field == 'rate_adjustment')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN rate_adjustment varchar(20) ", $en_table_name));
        }

        $query_display_preference = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'display_preference'");
        if (!(isset($query_display_preference->Field) && $query_display_preference->Field == 'display_preference')) {
            $wpdb->query(sprintf('ALTER TABLE %s ADD COLUMN display_preference enum("no","description","distance") ', $en_table_name));
        }

        $query_address_type = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'address_type'");
        if (!(isset($query_address_type->Field) && $query_address_type->Field == 'address_type')) {
            $wpdb->query(sprintf('ALTER TABLE %s ADD COLUMN address_type enum("both","commercial","residential") DEFAULT "both" ', $en_table_name));
        }

        $query_default_address_type = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'default_address_type'");
        if (!(isset($query_default_address_type->Field) && $query_default_address_type->Field == 'default_address_type')) {
            $wpdb->query(sprintf('ALTER TABLE %s ADD COLUMN default_address_type enum("residential","commercial") DEFAULT "commercial" ', $en_table_name));
        }

        $query_min_cart_value = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'min_cart_value'");
        if (!(isset($query_min_cart_value->Field) && $query_min_cart_value->Field == 'min_cart_value')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN min_cart_value float ", $en_table_name));
        }

        $query_max_cart_value = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'max_cart_value'");
        if (!(isset($query_max_cart_value->Field) && $query_max_cart_value->Field == 'max_cart_value')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN max_cart_value float ", $en_table_name));
        }

        $query_cart_value_type = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'cart_value_type'");
        if (!(isset($query_cart_value_type->Field) && $query_cart_value_type->Field == 'cart_value_type')) {
            $wpdb->query(sprintf('ALTER TABLE %s ADD COLUMN cart_value_type enum("total","profile") NOT NULL ', $en_table_name));
        }

        $query_base_amount = $wpdb->get_row("SHOW COLUMNS FROM " . $en_table_name . " LIKE 'base_amount'");
        if (!(isset($query_base_amount->Field) && $query_base_amount->Field == 'base_amount')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN base_amount decimal(6,2) DEFAULT 0.00", $en_table_name));
        }

        $this->make_the_rates_float_coulumns_nullable($wpdb, $en_table_name);
    }

    public function make_the_rates_float_coulumns_nullable($wpdb,$rates_table_name)
    {
        $columns = [
            'min_weight' => 'FLOAT',
            'max_weight' => 'FLOAT',
            'min_length' => 'FLOAT',
            'max_length' => 'FLOAT',
            'min_distance' => 'FLOAT',
            'max_distance' => 'FLOAT',
            'min_quote' => 'FLOAT',
            'max_quote' => 'FLOAT',
            'min_cart_value' => 'FLOAT',
            'max_cart_value' => 'FLOAT',
        ];

        foreach ($columns as $column => $type) {
            $result = $wpdb->get_row(
                $wpdb->prepare("
                SELECT IS_NULLABLE 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = %s
                  AND COLUMN_NAME = %s
            ", $rates_table_name, $column)
            );

            if ($result && $result->IS_NULLABLE !== 'YES') {
                $sql = "ALTER TABLE `$rates_table_name` MODIFY `$column` $type NULL";
                $wpdb->query($sql);
            }
        }
    }

    /**
     * Create table for zones relationship to shipping rate
     */
    public function create_en_zone_rates_table()
    {
        if (is_multisite() && $this->network_wide) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                global $wpdb;

                $en_charset_collate = $wpdb->get_charset_collate();
                $en_table_name = $wpdb->prefix . 'en_zone_rates';

                $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
			  id int(11) NOT NULL AUTO_INCREMENT,
			  zone_id int(11) NOT NULL,
			  dbsc_rate_id int(11) NOT NULL,
            PRIMARY KEY  (id)        
        )' . $en_charset_collate;

                $wpdb->query($en_created_table);
                $success = empty($wpdb->last_error);
                restore_current_blog();
            }
        }else {
            global $wpdb;

            $en_charset_collate = $wpdb->get_charset_collate();
            $en_table_name = $wpdb->prefix . 'en_zone_rates';

            $en_created_table = 'CREATE TABLE IF NOT EXISTS ' . $en_table_name . '( 
			  id int(11) NOT NULL AUTO_INCREMENT,
			  zone_id int(11) NOT NULL,
			  dbsc_rate_id int(11) NOT NULL,
            PRIMARY KEY  (id)        
        )' . $en_charset_collate;

            $wpdb->query($en_created_table);
            $success = empty($wpdb->last_error);
        }
    }

}
