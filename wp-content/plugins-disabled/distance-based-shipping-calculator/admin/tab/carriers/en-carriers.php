<?php
/**
 * Carriers show.
 */

namespace EnDistanceBaseShippingCarriers;

/**
 * Show and update carriers.
 * Class EnDistanceBaseShippingCarriers
 * @package EnDistanceBaseShippingCarriers
 */
class EnDistanceBaseShippingCarriers
{
    /**
     * Show Carriers
     */
    static public function en_load()
    {
        self::en_Done_carriers();
        $EN_DISTANCE_BASE_SHIPPING_carriers = EnDistanceBaseShippingCarriers::EN_DISTANCE_BASE_SHIPPING_carriers();
        echo '<div class="EN_DISTANCE_BASE_SHIPPING_carriers">';
        echo '<form method="post">';
        echo '<p>Identifies which carriers are included in the quote response, not what is displayed in the shopping cart. Identify what displays in the shopping cart in the Quote Settings. For example, you may include quote responses from all carriers, but elect to only show the cheapest three in the shopping cart.  <br> <br> Not all carriers service all origin and destination points. If a carrier doesn`t service the ship to address, it is automatically omitted from the quote response. Consider conferring with your Worldwide Express representative if you`d like to narrow the number of carrier responses.</p>';
        echo '<table>';
        echo '<tr>';
        echo '<th>Carrier Name</th>';
        echo '<th>Logo</th>';
        echo '<th> <input type="checkbox" id="EN_DISTANCE_BASE_SHIPPING_total_carriers"> </th>';
        echo '</tr>';
        $en_checked_carriers = get_option('EN_DISTANCE_BASE_SHIPPING_carriers');
        $en_checked_carriers = (isset($en_checked_carriers) && strlen($en_checked_carriers) > 0) ? json_decode($en_checked_carriers, true) : [];

        foreach ($EN_DISTANCE_BASE_SHIPPING_carriers as $key => $value) {

            $EN_DISTANCE_BASE_SHIPPING_carrier = in_array($value['en_standard_carrier_alpha_code'], $en_checked_carriers) ? "checked='checked'" : '';

            echo '<tr>';
            echo '<td> ' . esc_attr($value['EN_DISTANCE_BASE_SHIPPING_carrier_name']) . ' </td>';
            echo '<td> <img alt="carriers"  src="' . esc_attr(EN_DISTANCE_BASE_SHIPPING_DIR_FILE) . '/admin/tab/carriers/assets/' . esc_attr($value['EN_DISTANCE_BASE_SHIPPING_carrier_logo']) . '"> </td>';
            echo '<td> <input type="checkbox" class="EN_DISTANCE_BASE_SHIPPING_carrier" name="EN_DISTANCE_BASE_SHIPPING_carrier[]" value="' . esc_attr($value['en_standard_carrier_alpha_code']) . '" ' . $EN_DISTANCE_BASE_SHIPPING_carrier . '> </td>';
            echo '</tr>';
        }

        echo '</form>';
        echo '</table>';
        echo '</div>';
    }

    /**
     * Carriers Done Data
     */
    static public function en_Done_carriers()
    {
        if (isset($_POST['EN_DISTANCE_BASE_SHIPPING_carrier']) && (!empty($_POST['EN_DISTANCE_BASE_SHIPPING_carrier']))) {
            $EN_DISTANCE_BASE_SHIPPING_carrier = $_POST['EN_DISTANCE_BASE_SHIPPING_carrier'];
            update_option('EN_DISTANCE_BASE_SHIPPING_carriers', wp_json_encode($EN_DISTANCE_BASE_SHIPPING_carrier));

            echo "<script type='text/javascript'>
                window.location=document.location.href;
            </script>";
        }
    }

    /**
     * Carriers Data
     */
    static public function EN_DISTANCE_BASE_SHIPPING_carriers()
    {
        $carrier = [
            [
                'en_standard_carrier_alpha_code' => 'AACT',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'AAA COOPER',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'AACT.png'
            ], // AAA COOPER.
            [
                'en_standard_carrier_alpha_code' => 'ABFS',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'ABF FREIGHT SYSTEM, INC.',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'ABFS.png'
            ], // ABF FREIGHT SYSTEM, INC.
            [
                'en_standard_carrier_alpha_code' => 'DAFG',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'DAYTON FREIGHT LINES, INC.',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'DAFG.png'
            ], // DAYTON FREIGHT LINES, INC.
            [
                'en_standard_carrier_alpha_code' => 'DHRN',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'DOHRN TRANSFER COMPANY',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'DHRN.png'
            ], // DOHRN TRANSFER COMPANY.
            [
                'en_standard_carrier_alpha_code' => 'EQXT',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'EXPRESS 2000 INC',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'EQXT.png'
            ], // EXPRESS 2000 INC.
            [
                'en_standard_carrier_alpha_code' => 'FDXG',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'FED-EX GROUND',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'FDXG.png'
            ], // FED-EX GROUND.
            [
                'en_standard_carrier_alpha_code' => 'ODFL',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'OLD DOMINION FREIGHT LINE, INC.',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'ODFL.png'
            ], // OLD DOMINION FREIGHT LINE, INC.
            [
                'en_standard_carrier_alpha_code' => 'RDFS',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'ROADRUNNER TRANSPORTATION SERVICES',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'RDFS.png'
            ], // ROADRUNNER TRANSPORTATION SERVICES.
            [
                'en_standard_carrier_alpha_code' => 'SAIA',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'SAIA MOTOR FREIGHT LINE, INC.',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'SAIA.png'
            ], // SAIA MOTOR FREIGHT LINE, INC.
            [
                'en_standard_carrier_alpha_code' => 'SEFL',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'SOUTHEASTERN FREIGHT LINES, INC.',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'SEFL.png'
            ], // SOUTHEASTERN FREIGHT LINES, INC.
            [
                'en_standard_carrier_alpha_code' => 'UPSN',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'UPS GROUND',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'UPSN.png'
            ], // UPS GROUND.
            [
                'en_standard_carrier_alpha_code' => 'CNWY',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'XPO LOGISTICS FREIGHT, INC. (LTL)',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'CNWY.png'
            ], // XPO LOGISTICS FREIGHT, INC. (LTL).
            [
                'en_standard_carrier_alpha_code' => 'RDWY',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'YRC FREIGHT',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'RDWY.png'
            ], // YRC FREIGHT.
            [
                'en_standard_carrier_alpha_code' => 'RDTC',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'YRC FREIGHT - TIME CRITICAL',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'RDTC.png'
            ], // YRC FREIGHT - TIME CRITICAL.
            [
                'en_standard_carrier_alpha_code' => 'YRCA',
                'EN_DISTANCE_BASE_SHIPPING_carrier_name' => 'YRC FREIGHT ACCELERATED',
                'EN_DISTANCE_BASE_SHIPPING_carrier_logo' => 'YRCA.png'
            ], // YRC FREIGHT ACCELERATED.
        ];

        return $carrier;
    }
}