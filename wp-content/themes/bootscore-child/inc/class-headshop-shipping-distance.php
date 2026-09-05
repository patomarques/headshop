<?php
/**
 * Distance-based shipping method for Indicativa Headshop.
 *
 * Business rule:
 * - Up to N km AND cart subtotal >= free_min_total  -> free shipping.
 * - Up to N km but cart subtotal < free_min_total    -> flat rate.
 * - Beyond N km                                      -> real Correios quote
 *                                                        via Melhor Envio,
 *                                                        falling back to
 *                                                        price_per_km * distance
 *                                                        if the API call fails
 *                                                        or isn't configured.
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
    private $melhor_envio_client_id;
    private $melhor_envio_client_secret;
    private $melhor_envio_environment;

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
        $this->melhor_envio_client_id     = trim((string) $this->get_option('melhor_envio_client_id'));
        $this->melhor_envio_client_secret = trim((string) $this->get_option('melhor_envio_client_secret'));
        $this->melhor_envio_environment   = $this->get_option('melhor_envio_environment', 'sandbox');

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
                'description' => 'Usado apenas como reserva (fallback) se a cotação da Melhor Envio abaixo não estiver configurada ou falhar: distância total × este valor.',
                'default'     => '1.25',
                'custom_attributes' => ['step' => '0.01', 'min' => '0'],
                'desc_tip'    => true,
            ],
            'melhor_envio_section' => [
                'title'       => 'Correios via Melhor Envio (além do raio)',
                'type'        => 'title',
                'description' => 'Além do raio grátis, o frete é cotado de verdade nos Correios através da API da Melhor Envio (melhorenvio.com.br), em vez do preço por km acima. Cadastre um aplicativo em "Integrações &rarr; Área Dev" no painel deles, cole o Client ID e o Secret abaixo, salve, e depois clique em "Conectar" logo abaixo do Ambiente. Se a conexão não estiver feita (ou expirar), o cálculo cai automaticamente no preço por km.',
            ],
            'melhor_envio_client_id' => [
                'title'       => 'Client ID',
                'type'        => 'text',
                'description' => 'Da tela "Área Dev &rarr; Seus aplicativos" no painel da Melhor Envio.',
                'desc_tip'    => true,
            ],
            'melhor_envio_client_secret' => [
                'title'       => 'Client Secret',
                'type'        => 'password',
                'description' => 'Mesma tela, coluna "Secret".',
                'desc_tip'    => true,
            ],
            'melhor_envio_environment' => [
                'title'       => 'Ambiente',
                'type'        => 'select',
                'description' => 'Sandbox não gera cobrança real e serve para testar. Troque para Produção quando estiver pronto (é preciso reconectar ao trocar).',
                'default'     => 'sandbox',
                'options'     => [
                    'sandbox'    => 'Sandbox (testes)',
                    'production' => 'Produção',
                ],
                'desc_tip'    => true,
            ],
            'melhor_envio_status' => [
                'title'       => 'Conexão',
                'type'        => 'title',
                'description' => $this->get_melhor_envio_status_html(),
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
            $me_rate = $this->get_melhor_envio_rate($destination, $package);

            if ($me_rate) {
                $cost  = $me_rate['cost'];
                $label = $me_rate['label'];
            } else {
                // Melhor Envio not configured, or the API call failed —
                // fall back to the straight-line estimate so checkout
                // never breaks.
                $cost  = round($distance_km * $this->price_per_km, 2);
                $label = sprintf('%s (%.1f km)', $this->title, $distance_km);
            }
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
     * Real Correios quote via the Melhor Envio API, for addresses beyond
     * the free/flat-rate radius. Returns null (caller falls back to the
     * per-km estimate) if we're not connected, the destination postcode
     * is missing, or the API call fails for any reason.
     */
    private function get_melhor_envio_rate($destination, $package) {
        $access_token = $this->ensure_valid_access_token();
        if (!$access_token) {
            return null;
        }

        $to_postcode = preg_replace('/\D/', '', $destination['postcode'] ?? '');
        if (8 !== strlen($to_postcode)) {
            return null;
        }

        $from_postcode = preg_replace('/\D/', '', get_option('woocommerce_store_postcode', ''));
        if (8 !== strlen($from_postcode)) {
            return null;
        }

        $dims            = $this->build_package_dimensions($package);
        $insurance_value = round((float) ($package['contents_cost'] ?? 0), 2);

        $cache_key = 'headshop_me_' . md5(implode('|', [
            $from_postcode,
            $to_postcode,
            $dims['weight'],
            $dims['length'],
            $dims['width'],
            $dims['height'],
            $insurance_value,
            $this->melhor_envio_environment,
        ]));

        $cached = get_transient($cache_key);
        if (false !== $cached) {
            return $cached ?: null; // cached failure stored as empty string
        }

        $base_url = 'production' === $this->melhor_envio_environment
            ? 'https://melhorenvio.com.br'
            : 'https://sandbox.melhorenvio.com.br';

        $response = wp_remote_post($base_url . '/api/v2/me/shipment/calculate', [
            'timeout' => 10,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
                'User-Agent'    => 'Indicativa Headshop (' . get_option('admin_email') . ')',
            ],
            'body' => wp_json_encode([
                'from'    => ['postal_code' => $from_postcode],
                'to'      => ['postal_code' => $to_postcode],
                'package' => [
                    'weight' => $dims['weight'],
                    'width'  => $dims['width'],
                    'height' => $dims['height'],
                    'length' => $dims['length'],
                ],
                'options' => [
                    'insurance_value' => $insurance_value,
                    'receipt'         => false,
                    'own_hand'        => false,
                ],
            ]),
        ]);

        // Retry sooner on failure (10 min) than on a good quote (1 hour),
        // in case it's a transient outage/misconfiguration being fixed.
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            set_transient($cache_key, '', 10 * MINUTE_IN_SECONDS);
            return null;
        }

        $options = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($options)) {
            set_transient($cache_key, '', 10 * MINUTE_IN_SECONDS);
            return null;
        }

        $cheapest = null;
        foreach ($options as $option) {
            if (!empty($option['error']) || empty($option['price'])) {
                continue;
            }

            $is_correios = 'Correios' === ($option['company']['name'] ?? '');
            $price       = (float) $option['price'];

            // Prefer any Correios service over non-Correios; within the
            // same tier, prefer the cheapest.
            $better = !$cheapest
                || ($is_correios && !$cheapest['is_correios'])
                || ($is_correios === $cheapest['is_correios'] && $price < $cheapest['price']);

            if ($better) {
                $cheapest = [
                    'price'       => $price,
                    'name'        => $option['name'] ?? 'Entrega',
                    'company'     => $option['company']['name'] ?? '',
                    'is_correios' => $is_correios,
                ];
            }
        }

        if (!$cheapest) {
            set_transient($cache_key, '', 10 * MINUTE_IN_SECONDS);
            return null;
        }

        $result = [
            'cost'  => round($cheapest['price'], 2),
            'label' => sprintf('%s (%s)', $this->title, trim($cheapest['company'] . ' ' . $cheapest['name'])),
        ];

        set_transient($cache_key, $result, HOUR_IN_SECONDS);

        return $result;
    }

    /**
     * Ensures we have a non-expired Melhor Envio access token, refreshing
     * it via the stored refresh token when needed. Returns '' (caller
     * falls back to the per-km estimate) if we're not connected, the
     * connection is for a different environment than the one currently
     * selected, or the refresh call fails.
     */
    private function ensure_valid_access_token() {
        $access_token  = get_option('headshop_me_access_token', '');
        $expires_at    = (int) get_option('headshop_me_expires_at', 0);
        $refresh_token = get_option('headshop_me_refresh_token', '');
        $connected_env = get_option('headshop_me_environment', '');

        if ($connected_env !== $this->melhor_envio_environment) {
            return '';
        }

        if ($access_token && $expires_at > time() + 60) {
            return $access_token;
        }

        if (!$refresh_token || empty($this->melhor_envio_client_id) || empty($this->melhor_envio_client_secret)) {
            return '';
        }

        $response = wp_remote_post($this->get_melhor_envio_base_url() . '/oauth/token', [
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            'body'    => wp_json_encode([
                'grant_type'    => 'refresh_token',
                'client_id'     => $this->melhor_envio_client_id,
                'client_secret' => $this->melhor_envio_client_secret,
                'refresh_token' => $refresh_token,
            ]),
        ]);

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return '';
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($data['access_token'])) {
            return '';
        }

        update_option('headshop_me_access_token', $data['access_token'], false);
        update_option('headshop_me_refresh_token', $data['refresh_token'] ?? $refresh_token, false);
        update_option('headshop_me_expires_at', time() + (int) ($data['expires_in'] ?? 2592000), false);

        return $data['access_token'];
    }

    private function get_melhor_envio_base_url() {
        return 'production' === $this->melhor_envio_environment
            ? 'https://melhorenvio.com.br'
            : 'https://sandbox.melhorenvio.com.br';
    }

    /**
     * Builds the "Conectar" link (OAuth authorize URL) and stores a
     * short-lived state nonce to validate the callback. Returns '' if
     * the Client ID hasn't been saved yet.
     */
    private function get_melhor_envio_authorize_url() {
        if (empty($this->melhor_envio_client_id)) {
            return '';
        }

        $state = wp_generate_password(32, false);
        set_transient('headshop_me_oauth_state', [
            'state'       => $state,
            'environment' => $this->melhor_envio_environment,
        ], 10 * MINUTE_IN_SECONDS);

        return $this->get_melhor_envio_base_url() . '/oauth/authorize?' . http_build_query([
            'client_id'     => $this->melhor_envio_client_id,
            'redirect_uri'  => home_url('/'),
            'response_type' => 'code',
            'scope'         => 'shipping-calculate',
            'state'         => $state,
        ]);
    }

    /**
     * Connection status box shown in the admin settings screen (rendered
     * as the description of a 'title' field, so plain HTML is fine here).
     */
    private function get_melhor_envio_status_html() {
        $access_token  = get_option('headshop_me_access_token', '');
        $expires_at    = (int) get_option('headshop_me_expires_at', 0);
        $connected_env = get_option('headshop_me_environment', '');

        if ($access_token && $expires_at > time()) {
            $env_label = 'production' === $connected_env ? 'Produção' : 'Sandbox';
            $html = '<p style="color:#1e7e34;font-weight:600;">&#10003; Conectado (' . esc_html($env_label) . ') &mdash; token válido até ' . esc_html(date_i18n('d/m/Y H:i', $expires_at)) . '.</p>';

            if ($connected_env !== $this->melhor_envio_environment) {
                $html .= '<p style="color:#a94442;">O ambiente conectado (' . esc_html($env_label) . ') é diferente do selecionado acima. Reconecte depois de salvar.</p>';
            }
        } else {
            $html = '<p>Ainda não conectado.</p>';
        }

        $authorize_url = $this->get_melhor_envio_authorize_url();

        if ($authorize_url) {
            $label = $access_token ? 'Reconectar' : 'Conectar à Melhor Envio';
            $html .= '<p><a href="' . esc_url($authorize_url) . '" class="button button-primary">' . esc_html($label) . '</a></p>';
        } else {
            $html .= '<p><em>Preencha e salve o Client ID acima primeiro.</em></p>';
        }

        return $html;
    }

    /**
     * Aggregates cart contents into a single package weight/dimensions
     * for the Melhor Envio quote. Not true bin-packing — items are
     * "stacked" (heights summed, largest length/width kept) — but close
     * enough for an estimate, with Correios' minimum box size as a floor
     * for products missing shipping data.
     */
    private function build_package_dimensions($package) {
        $total_weight = 0.0;
        $max_length   = 16.0; // Correios minimum package size (cm)
        $max_width    = 11.0;
        $sum_height   = 2.0;

        foreach ($package['contents'] ?? [] as $item) {
            $product = $item['data'] ?? null;
            if (!$product) {
                continue;
            }

            $qty    = max(1, (int) $item['quantity']);
            $weight = (float) $product->get_weight() ?: 0.3;
            $length = (float) $product->get_length() ?: 16;
            $width  = (float) $product->get_width()  ?: 11;
            $height = (float) $product->get_height() ?: 4;

            $total_weight += $weight * $qty;
            $max_length    = max($max_length, $length);
            $max_width     = max($max_width, $width);
            $sum_height   += $height * $qty;
        }

        return [
            'weight' => max(0.1, round($total_weight, 2)),
            'length' => (int) min(105, round($max_length)),
            'width'  => (int) min(105, round($max_width)),
            'height' => (int) min(105, round($sum_height)),
        ];
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

    /**
     * Finds the instance_id this method is actually configured under, by
     * scanning shipping zones. Used by the OAuth callback below, which
     * runs outside of any particular method instance's own context.
     */
    public static function get_configured_instance_id() {
        if (!class_exists('WC_Shipping_Zones')) {
            return 0;
        }

        foreach (WC_Shipping_Zones::get_zones() as $zone) {
            foreach ($zone['shipping_methods'] as $method) {
                if ('headshop_distance_shipping' === $method->id) {
                    return (int) $method->instance_id;
                }
            }
        }

        return 0;
    }

    public static function get_instance_settings($instance_id) {
        return get_option('woocommerce_headshop_distance_shipping_' . (int) $instance_id . '_settings', []);
    }

    /**
     * Admin URL for this method's shipping zone, so the OAuth callback
     * can send the admin back to where they clicked "Conectar".
     */
    public static function get_zone_admin_url() {
        if (class_exists('WC_Shipping_Zones')) {
            foreach (WC_Shipping_Zones::get_zones() as $zone) {
                foreach ($zone['shipping_methods'] as $method) {
                    if ('headshop_distance_shipping' === $method->id) {
                        return admin_url('admin.php?page=wc-settings&tab=shipping&zone_id=' . $zone['id']);
                    }
                }
            }
        }

        return admin_url('admin.php?page=wc-settings&tab=shipping');
    }
}

/**
 * Melhor Envio OAuth callback. The app's redirect_uri is registered as
 * the site's root URL (Melhor Envio doesn't allow a custom path), so we
 * hook the earliest front-end request point and only act when the
 * expected ?code&state params are present and the state matches what
 * get_melhor_envio_authorize_url() stored — otherwise this is just a
 * normal homepage visit and we get out of the way immediately.
 */
add_action('template_redirect', 'headshop_melhor_envio_oauth_callback');
function headshop_melhor_envio_oauth_callback() {
    if (empty($_GET['code']) || empty($_GET['state']) || !is_front_page()) {
        return;
    }

    $stored = get_transient('headshop_me_oauth_state');
    if (!$stored || !hash_equals((string) $stored['state'], (string) wp_unslash($_GET['state']))) {
        return;
    }
    delete_transient('headshop_me_oauth_state');

    $redirect_to = Headshop_Shipping_Distance::get_zone_admin_url();
    $instance_id = Headshop_Shipping_Distance::get_configured_instance_id();
    $settings    = Headshop_Shipping_Distance::get_instance_settings($instance_id);

    $client_id     = $settings['melhor_envio_client_id'] ?? '';
    $client_secret = $settings['melhor_envio_client_secret'] ?? '';
    $environment   = $stored['environment'];

    if (empty($client_id) || empty($client_secret)) {
        wp_safe_redirect(add_query_arg('headshop_me', 'error', $redirect_to));
        exit;
    }

    $base_url = 'production' === $environment
        ? 'https://melhorenvio.com.br'
        : 'https://sandbox.melhorenvio.com.br';

    $response = wp_remote_post($base_url . '/oauth/token', [
        'timeout' => 15,
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'body'    => wp_json_encode([
            'grant_type'    => 'authorization_code',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => home_url('/'),
            'code'          => sanitize_text_field(wp_unslash($_GET['code'])),
        ]),
    ]);

    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
        wp_safe_redirect(add_query_arg('headshop_me', 'error', $redirect_to));
        exit;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($data['access_token'])) {
        wp_safe_redirect(add_query_arg('headshop_me', 'error', $redirect_to));
        exit;
    }

    update_option('headshop_me_access_token', $data['access_token'], false);
    update_option('headshop_me_refresh_token', $data['refresh_token'] ?? '', false);
    update_option('headshop_me_expires_at', time() + (int) ($data['expires_in'] ?? 2592000), false);
    update_option('headshop_me_environment', $environment, false);

    wp_safe_redirect(add_query_arg('headshop_me', 'connected', $redirect_to));
    exit;
}
