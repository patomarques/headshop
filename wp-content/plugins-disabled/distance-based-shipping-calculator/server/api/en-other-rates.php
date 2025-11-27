<?php
/**
 * Third party rates used in eniture shipping services.
 */

namespace EnDistanceBaseShippingOtherRates;

/**
 * Filter other rates will be shown on the cart|checkout page.
 * Class EnDistanceBaseShippingOtherRates
 * @package EnDistanceBaseShippingOtherRates
 */
class EnDistanceBaseShippingOtherRates
{
    /**
     * @param array $instor_pickup_local_delivery
     * @param string $en_is_shipment
     * @param array $en_origin_address
     * @param array $api_rates
     * @param array $en_settings
     * @return array
     */
    static public function en_extra_custom_services($instor_pickup_local_delivery,
                                                    $en_is_shipment,
                                                    $en_origin_address,
                                                    $api_rates,
                                                    $en_settings)
    {
        $rates = [];
        if (!empty($instor_pickup_local_delivery) && $en_is_shipment === 'en_single_shipment') {
            $senderDescInStorePickup = $senderDescLocalDelivery = $suppressOtherRates = '';
            $feeLocalDelivery = 0;
            extract($en_origin_address, null);

            if (isset($instor_pickup_local_delivery['inStorePickup']['status']) &&
                $instor_pickup_local_delivery['inStorePickup']['status'] === '1') {
                $rates[] = array(
                    'id' => 'in-store-pick-up',
                    'cost' => 0,
                    'label' => strlen($senderDescInStorePickup) > 0 ? $senderDescInStorePickup : 'In-store pick up',
                );
            }

            if (isset($instor_pickup_local_delivery['localDelivery']['status']) &&
                $instor_pickup_local_delivery['localDelivery']['status'] === '1') {
                $rates[] = array(
                    'id' => 'local-delivery',
                    'cost' => $feeLocalDelivery > 0 ? $feeLocalDelivery : 0,
                    'label' => strlen($senderDescLocalDelivery) > 0 ? $senderDescLocalDelivery : 'Local delivery',
                );
            }

            if ($suppressOtherRates == 'on' && !empty($rates)) {
                $api_rates = [];
            }
        }

        if ($en_settings['own_freight'] === 'yes') {
            $own_freight_label = (strlen($en_settings['own_freight_label']) > 0) ?
                $en_settings['own_freight_label'] : "I'll arrange my own freight";

            $rates[] = [
                'id' => 'en_own_freight',
                'cost' => 0,
                'label' => $own_freight_label,
            ];
        }

        $api_rates = array_merge($rates, $api_rates);

        return $api_rates;
    }
}