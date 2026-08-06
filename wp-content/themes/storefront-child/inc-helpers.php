<?php
function eletronicos_category_icon($slug) {
    $icons = [
        'resistores' => '<svg viewBox="0 0 80 40" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><line x1="0" y1="20" x2="12" y2="20"/><polyline points="12,20 17,6 23,34 29,6 35,34 41,6 47,34 52,20"/><line x1="52" y1="20" x2="80" y2="20"/></svg>',

        'capacitores' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg"><line x1="4" y1="32" x2="24" y2="32" stroke-width="2.5"/><line x1="24" y1="14" x2="24" y2="50" stroke-width="4"/><line x1="32" y1="14" x2="32" y2="50" stroke-width="4"/><line x1="32" y1="32" x2="60" y2="32" stroke-width="2.5"/></svg>',

        'diodos' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><line x1="4" y1="32" x2="18" y2="32"/><polygon points="18,14 18,50 46,32" fill="currentColor" opacity="0.2"/><polygon points="18,14 18,50 46,32" stroke="currentColor"/><line x1="46" y1="14" x2="46" y2="50"/><line x1="46" y1="32" x2="60" y2="32"/></svg>',

        'transistores' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><line x1="4" y1="32" x2="26" y2="32"/><line x1="26" y1="14" x2="26" y2="50" stroke-width="4"/><line x1="26" y1="22" x2="54" y2="10"/><line x1="26" y1="42" x2="54" y2="54"/><line x1="54" y1="10" x2="54" y2="18"/><line x1="54" y1="46" x2="54" y2="54"/><polyline points="44,50 54,54 50,43"/></svg>',

        'circuitos-integrados' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg"><rect x="16" y="10" width="32" height="44" rx="2"/><line x1="4" y1="20" x2="16" y2="20"/><line x1="4" y1="32" x2="16" y2="32"/><line x1="4" y1="44" x2="16" y2="44"/><line x1="48" y1="20" x2="60" y2="20"/><line x1="48" y1="32" x2="60" y2="32"/><line x1="48" y1="44" x2="60" y2="44"/><circle cx="22" cy="16" r="2.5" fill="currentColor"/></svg>',

        'conectores' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><rect x="6" y="18" width="36" height="28" rx="3"/><line x1="42" y1="26" x2="58" y2="26"/><line x1="42" y1="38" x2="58" y2="38"/><line x1="16" y1="18" x2="16" y2="8"/><line x1="24" y1="18" x2="24" y2="8"/><line x1="32" y1="18" x2="32" y2="8"/></svg>',

        'ferramentas' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><circle cx="46" cy="18" r="10"/><line x1="38" y1="26" x2="16" y2="48"/><circle cx="12" cy="52" r="8"/></svg>',

        'sensores' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg"><line x1="32" y1="36" x2="32" y2="56" stroke-width="2.5"/><circle cx="32" cy="30" r="6" stroke-width="2.5"/><path d="M20,46 Q12,30 20,14"/><path d="M44,46 Q52,30 44,14"/><path d="M14,52 Q4,30 14,8"/><path d="M50,52 Q60,30 50,8"/></svg>',
    ];

    $default = '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" xmlns="http://www.w3.org/2000/svg"><circle cx="32" cy="32" r="20"/><polyline points="32,22 32,32 40,40" stroke-linejoin="round"/><circle cx="32" cy="32" r="2" fill="currentColor"/></svg>';

    return $icons[$slug] ?? $default;
}

add_filter('get_product_search_form', function () {
    $action = esc_url(home_url('/'));
    $placeholder = esc_attr__('Buscar produtos...', 'storefront-child');
    $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.099zm-5.44.658a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z"/></svg>';
    return '<form role="search" method="get" class="header-search-form" action="' . $action . '">'
        . '<input type="search" class="search-field" placeholder="' . $placeholder . '" name="s">'
        . '<input type="hidden" name="post_type" value="product">'
        . '<button type="submit" aria-label="Buscar">' . $icon . '</button>'
        . '</form>';
});

function eletronicos_payment_logos_svg() {
    return [
        'Visa'      => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#1a1f71"/><text x="28" y="24" text-anchor="middle" fill="white" font-size="16" font-weight="900" font-style="italic" font-family="Arial,sans-serif" letter-spacing="-0.5">VISA</text></svg>',
        'Master'    => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#252525"/><circle cx="22" cy="18" r="11" fill="#eb001b"/><circle cx="34" cy="18" r="11" fill="#f79e1b" opacity="0.88"/></svg>',
        'Elo'       => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#ffcb04"/><text x="28" y="25" text-anchor="middle" fill="#1a1a1a" font-size="18" font-weight="900" font-family="Arial,sans-serif">elo</text></svg>',
        'Amex'      => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#2671b3"/><text x="28" y="19" text-anchor="middle" fill="white" font-size="9.5" font-weight="900" font-family="Arial,sans-serif" letter-spacing="0.5">AMERICAN</text><text x="28" y="30" text-anchor="middle" fill="white" font-size="9" font-weight="700" font-family="Arial,sans-serif" letter-spacing="1">EXPRESS</text></svg>',
        'Diners'    => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#0f3564"/><text x="28" y="19" text-anchor="middle" fill="white" font-size="10" font-weight="900" font-family="Arial,sans-serif" letter-spacing="0.5">DINERS</text><text x="28" y="30" text-anchor="middle" fill="white" font-size="9" font-weight="500" font-family="Arial,sans-serif" letter-spacing="2">CLUB</text></svg>',
        'Hipercard' => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#b3131b"/><text x="28" y="19" text-anchor="middle" fill="white" font-size="9" font-weight="900" font-family="Arial,sans-serif" letter-spacing="0.3">HIPER</text><text x="28" y="30" text-anchor="middle" fill="white" font-size="9" font-weight="900" font-family="Arial,sans-serif" letter-spacing="0.3">CARD</text></svg>',
        'Discover'  => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#fff" stroke="#ddd" stroke-width="1"/><circle cx="36" cy="18" r="11" fill="#f76f20"/><text x="16" y="22" text-anchor="middle" fill="#231f20" font-size="7" font-weight="900" font-family="Arial,sans-serif" letter-spacing="0">DIS</text></svg>',
        'Pix'       => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#32bcad"/><text x="28" y="25" text-anchor="middle" fill="white" font-size="16" font-weight="900" font-family="Arial,sans-serif" letter-spacing="1">PIX</text></svg>',
        'Boleto'    => '<svg viewBox="0 0 56 36" xmlns="http://www.w3.org/2000/svg"><rect width="56" height="36" rx="4" fill="#1a1a1a"/><rect x="8" y="10" width="3" height="16" fill="white"/><rect x="13" y="10" width="1.5" height="16" fill="white"/><rect x="16" y="10" width="3" height="16" fill="white"/><rect x="21" y="10" width="1.5" height="16" fill="white"/><rect x="24" y="10" width="4" height="16" fill="white"/><rect x="30" y="10" width="1.5" height="16" fill="white"/><rect x="33" y="10" width="3" height="16" fill="white"/><rect x="38" y="10" width="1.5" height="16" fill="white"/><rect x="41" y="10" width="3" height="16" fill="white"/><rect x="46" y="10" width="2" height="16" fill="white"/></svg>',
    ];
}

add_action( 'init', function () {
    if ( get_option( 'eletronicos_payment_logos_seeded' ) ) {
        return;
    }

    $entries = [
        [ 'title' => 'Visa',       'key' => 'Visa',      'order' => 1 ],
        [ 'title' => 'Mastercard', 'key' => 'Master',    'order' => 2 ],
        [ 'title' => 'Elo',        'key' => 'Elo',       'order' => 3 ],
        [ 'title' => 'Amex',       'key' => 'Amex',      'order' => 4 ],
        [ 'title' => 'Diners',     'key' => 'Diners',    'order' => 5 ],
        [ 'title' => 'Hipercard',  'key' => 'Hipercard', 'order' => 6 ],
        [ 'title' => 'Discover',   'key' => 'Discover',  'order' => 7 ],
        [ 'title' => 'Pix',        'key' => 'Pix',       'order' => 8 ],
        [ 'title' => 'Boleto',     'key' => 'Boleto',    'order' => 9 ],
    ];

    foreach ( $entries as $entry ) {
        $id = wp_insert_post( [
            'post_type'   => 'payment_logo',
            'post_title'  => $entry['title'],
            'post_status' => 'publish',
            'menu_order'  => $entry['order'],
        ] );
        if ( $id && ! is_wp_error( $id ) ) {
            update_post_meta( $id, '_asaas_key', $entry['key'] );
        }
    }

    update_option( 'eletronicos_payment_logos_seeded', true );
} );
