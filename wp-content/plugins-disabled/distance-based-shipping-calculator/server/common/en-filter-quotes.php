<?php
/**
 * Filter rates.
 */

namespace EnDistanceBaseShippingFilterQuotes;

use EnDistanceBaseShippingVersionCompact\EnDistanceBaseShippingVersionCompact;

/**
 * Rates according selected rating method.
 * Class EnDistanceBaseShippingFilterQuotes
 * @package EnDistanceBaseShippingFilterQuotes
 */
class EnDistanceBaseShippingFilterQuotes
{
    static public $quotes;
    static public $quote_settings;
    static public $total_carriers;

    /**
     * set values in class attributes and return quotes
     * @param array $quotes
     * @param array $quote_settings
     * @return array
     */
    static public function calculate_quotes($quotes, $quote_settings)
    {
        self::$quotes = $quotes;
        self::$quote_settings = $quote_settings;
        self::$total_carriers = self::$quote_settings['total_carriers'];

        $rating_method = self::$quote_settings['rating_method'];
        return self::$rating_method();
    }

    /**
     * Get random id for quote
     * @return string
     */
    static public function rand_string()
    {
        $alphabets = 'abcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle(str_repeat($alphabets, mt_rand(1, 10))), 1, 10);
    }

    /**
     * calculate average for quotes
     * @return array type
     */
    static public function average_rate()
    {
        self::$quotes = (isset(self::$quotes) && (is_array(self::$quotes))) ?
            array_slice(self::$quotes, 0, self::$total_carriers) : [];
        $rate_list = EnDistanceBaseShippingVersionCompact::en_array_column(self::$quotes, 'cost');
        $rate_sum = array_sum($rate_list) / count(self::$quotes);
        $quotes_reset = reset(self::$quotes);

        $label = (isset($quotes_reset['label'])) ? $quotes_reset['label'] : "Shipping";
        $append_str = str_replace(' ', '', esc_attr($$label));
        $id = 'en_dbsc_'.$append_str;
        $rate[] = array(
            'id' => $id,
            'cost' => $rate_sum,
            'markup' => (isset($quotes_reset['markup'])) ? $quotes_reset['markup'] : "",
            'label' => $label,
            'meta_data' => (isset($quotes_reset['meta_data'])) ? $quotes_reset['meta_data'] : [],
            'label_sufex' => (isset($quotes_reset['label_sufex'])) ? $quotes_reset['label_sufex'] : [],
            'append_label' => (isset($quotes_reset['append_label'])) ? $quotes_reset['append_label'] : "",
        );

        return $rate;
    }

    /**
     * calculate cheapest rate
     * @return array
     */
    static public function Cheapest()
    {
        return (isset(self::$quotes) && (is_array(self::$quotes))) ? array_slice(self::$quotes, 0, 1) : [];
    }

    /**
     * calculate cheapest rate numbers
     * @return array type
     */
    static public function cheapest_options()
    {
        return (isset(self::$quotes) && (is_array(self::$quotes))) ? array_slice(self::$quotes, 0, self::$total_carriers) : [];
    }
}
