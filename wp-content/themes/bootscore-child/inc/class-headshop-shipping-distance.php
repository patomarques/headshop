<?php
/**
 * Distance-based shipping method for Indicativa Headshop.
 *
 * Business rule:
 * - Up to N km AND cart subtotal >= free_min_total  -> free shipping.
 * - Up to N km but cart subtotal < free_min_total    -> flat rate.
 * - Beyond N km                                      -> price_per_km * distance.
 *
 * Distance is straight-line (haversine) between the store's origin
 * coordinates and the customer's shipping address, geocoded via the
 * free Nominatim (OpenStreetMap) API. Results are cached in transients
 * to respect Nominatim's usage policy (max ~1 req/s, no hammering).
 */

defined('ABSPATH') || exit;

if (class_exists('Headshop_Shipping_Distance')) {
    return;
}

class Headshop_Shipping_Distance extends WC_Shipping_Method {

    private $origin_lat;
    private $origin_lng;
    private $free_max_distance;
    private $free_min_total;
    private $flat_rate_near;
    private $price_per_km;

    public function __construct($instance_id = 0) {
        $this->id                 = 'headshop_distance_shipping';
        $this->instance_id        = absint($instance_id);
        $this->method_title       = 'Frete por distância (Indicativa)';
        $this->method_description = 'Grátis perto da loja acima de um valor mínimo, taxa fixa perto abaixo do mínimo, e cobrança por km além do raio configurado.';
        $this->supports            = ['shipping-zones', 'instance-settings'];

        $this->init();
    }

    private function init() {
        $this->init_form_fields();
        $this->init_settings();

        $this->title              = $this->get_option('title');
        $this->tax_status         = 'taxable';
        $this->origin_lat         = (float) $this->get_option('origin_lat');
        $this->origin_lng         = (float) $this->get_option('origin_lng');
        $this->free_max_distance  = (float) $this->get_option('free_max_distance');
        $this->free_min_total     = (float) $this->get_option('free_min_total');
        $this->flat_rate_near     = (float) $this->get_option('flat_rate_near');
        $this->price_per_km       = (float) $this->get_option('price_per_km');

        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields() {
        $this->instance_form_fields = [
            'title' => [
                'title'       => 'Título',
                'type'        => 'text',
                'description' => 'Nome exibido para o cliente no checkout.',
                'default'     => 'Entrega',
                'desc_tip'    => true,
            ],
            'origin_lat' => [
                'title'       => 'Latitude da loja',
                'type'        => 'text',
                'description' => 'Coordenada de origem para o cálculo de distância. Pré-preenchida com o endereço da loja (Rua Tupy, 147, Salgado, Caruaru-PE).',
                'default'     => '-8.2790239',
                'desc_tip'    => true,
            ],
            'origin_lng' => [
                'title'       => 'Longitude da loja',
                'type'        => 'text',
                'default'     => '-35.9655235',
                'desc_tip'    => true,
            ],
            'free_max_distance' => [
                'title'       => 'Raio grátis (km)',
                'type'        => 'number',
                'description' => 'Distância máxima, em km, dentro da qual o frete pode ser grátis ou taxa fixa.',
                'default'     => '5',
                'custom_attributes' => ['step' => '0.1', 'min' => '0'],
                'desc_tip'    => true,
            ],
            'free_min_total' => [
                'title'       => 'Valor mínimo p/ frete grátis (R$)',
                'type'        => 'number',
                'description' => 'Dentro do raio grátis, pedidos com subtotal igual ou maior que este valor não pagam frete.',
                'default'     => '20',
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
                'desc_tip'    => true,
            ],
            'flat_rate_near' => [
                'title'       => 'Taxa fixa dentro do raio (R$)',
                'type'        => 'number',
                'description' => 'Cobrado quando o endereço está dentro do raio grátis, mas o pedido não atinge o valor mínimo.',
                'default'     => '10',
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
                'desc_tip'    => true,
            ],
            'price_per_km' => [
                'title'       => 'Preço por km além do raio (R$)',
                'type'        => 'number',
                'description' => 'Cobrado por km quando o endereço está além do raio grátis (distância total × este valor).',
                'default'     => '1.25',
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
                'desc_tip'    => true,
            ],
        ];
    }

    public function calculate_shipping($package = []) {
        $destination = $package['destination'] ?? [];

        if (empty($destination['postcode']) || empty($destination['city'])) {
            return; // not enough address info yet to geocode
        }

        $dest_coords = $this->geocode_destination($destination);

        if (!$dest_coords || !$this->origin_lat || !$this->origin_lng) {
            // Geocoding failed (or origin misconfigured) — fall back to the
            // near-zone flat rate rather than blocking checkout entirely.
            $this->add_rate([
                'id'      => $this->get_rate_id(),
                'label'   => $this->title,
                'cost'    => $this->flat_rate_near,
                'package' => $package,
            ]);
            return;
        }

        $distance_km = $this->haversine_km(
            $this->origin_lat,
            $this->origin_lng,
            $dest_coords['lat'],
            $dest_coords['lng']
        );

        $cart_total = (float) ($package['contents_cost'] ?? 0);

        if ($distance_km <= $this->free_max_distance) {
            if ($cart_total >= $this->free_min_total) {
                $cost  = 0;
                $label = $this->title . ' (grátis)';
            } else {
                $cost  = $this->flat_rate_near;
                $label = $this->title;
            }
        } else {
            $cost  = round($distance_km * $this->price_per_km, 2);
            $label = sprintf('%s (%.1f km)', $this->title, $distance_km);
        }

        $this->add_rate([
            'id'      => $this->get_rate_id(),
            'label'   => $label,
            'cost'    => $cost,
            'package' => $package,
        ]);
    }

    /**
     * Geocode the customer's destination address via Nominatim (OSM),
     * caching the result per postcode+city for 30 days to stay within
     * Nominatim's fair-use policy (no repeat lookups per keystroke).
     */
    private function geocode_destination($destination) {
        $address_parts = array_filter([
            $destination['address_1'] ?? '',
            $destination['city'] ?? '',
            $destination['state'] ?? '',
            $destination['postcode'] ?? '',
            'Brazil',
        ]);
        $query = implode(', ', $address_parts);

        $cache_key = 'headshop_geocode_' . md5(strtolower($query));
        $cached    = get_transient($cache_key);
        if (false !== $cached) {
            return $cached ?: null; // cached null-result stored as empty string
        }

        $url = add_query_arg([
            'format'       => 'json',
            'q'            => $query,
            'countrycodes' => 'br',
            'limit'        => 1,
        ], 'https://nominatim.openstreetmap.org/search');

        $response = wp_remote_get($url, [
            'timeout'    => 8,
            'user-agent' => 'IndicativaHeadshop-WooCommerce/1.0 (' . home_url() . ')',
        ]);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            set_transient($cache_key, '', HOUR_IN_SECONDS); // retry sooner on failure
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body[0]['lat']) || empty($body[0]['lon'])) {
            set_transient($cache_key, '', DAY_IN_SECONDS);
            return null;
        }

        $coords = [
            'lat' => (float) $body[0]['lat'],
            'lng' => (float) $body[0]['lon'],
        ];

        set_transient($cache_key, $coords, 30 * DAY_IN_SECONDS);

        return $coords;
    }

    /**
     * Straight-line distance in km between two lat/lng points.
     */
    private function haversine_km($lat1, $lng1, $lat2, $lng2) {
        $earth_radius_km = 6371;

        $d_lat = deg2rad($lat2 - $lat1);
        $d_lng = deg2rad($lng2 - $lng1);

        $a = sin($d_lat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($d_lng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius_km * $c;
    }
}
