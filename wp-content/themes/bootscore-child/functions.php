<?php
/**
 * Bootscore Child — Headshop
 *
 * Replaces the default bootscore-child functions.php with all
 * homepage features ported from the wp-headshop Storefront child theme,
 * now using Bootstrap 5 natively.
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

defined('ABSPATH') || exit;


/* =====================================================================
   1. ENQUEUE STYLES & SCRIPTS
   ===================================================================== */

// Desativa o compilador SCSS do Bootscore para evitar erro fatal
// e usar apenas o CSS já pré-compilado em assets/css/main.css.
if (!defined('BOOTSCORE_SCSS_DISABLE_COMPILER')) {
    define('BOOTSCORE_SCSS_DISABLE_COMPILER', true);
}
add_filter('bootscore/scss/disable_compiler', '__return_true');

// Self-hosted fonts (assets/fonts) — preload so the browser starts
// fetching them before it even parses main.css, avoiding the FOUT that
// happened when Syne/Oswald were pulled from Google Fonts on each load.
add_action('wp_head', function () {
    $fonts_uri = get_stylesheet_directory_uri() . '/assets/fonts';
    echo '<link rel="preload" href="' . esc_url($fonts_uri . '/oswald-variable-latin.woff2') . '" as="font" type="font/woff2" crossorigin>' . "\n";
    echo '<link rel="preload" href="' . esc_url($fonts_uri . '/syne-variable-latin.woff2') . '" as="font" type="font/woff2" crossorigin>' . "\n";
}, 1);

add_action('wp_enqueue_scripts', 'headshop_enqueue_assets');
function headshop_enqueue_assets() {
    // Parent style
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Compiled child main.css (Bootstrap + custom SCSS + @font-face for Syne/Oswald)
    $css_path = get_stylesheet_directory() . '/assets/css/main.css';
    $css_ver  = file_exists($css_path) ? date('YmdHi', filemtime($css_path)) : null;
    wp_enqueue_style('headshop-main', get_stylesheet_directory_uri() . '/assets/css/main.css', array('parent-style'), $css_ver);

    // Dashicons (for cart icons)
    wp_enqueue_style('dashicons');

    // Custom JS
    $js_path = get_stylesheet_directory() . '/assets/js/custom.js';
    $js_ver  = file_exists($js_path) ? date('YmdHi', filemtime($js_path)) : null;
    wp_enqueue_script('headshop-custom', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery'), $js_ver, true);

    // Localize for AJAX
    if (class_exists('WooCommerce')) {
        wp_localize_script('headshop-custom', 'headshopAjax', array(
            'url'      => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('headshop_cart'),
            'debug'    => (bool) (defined('WP_DEBUG') && WP_DEBUG),
            'themeUrl' => get_stylesheet_directory_uri(),
        ));
    }
}


add_filter('body_class', function ($classes) {
    if (function_exists('is_cart') && is_cart() && class_exists('WooCommerce') && WC()->cart->is_empty()) {
        $classes[] = 'cart-is-empty';
    }
    return $classes;
});

// Force pt-BR for WooCommerce strings that may not translate via .mo
add_filter('gettext', function ($translation, $text, $domain) {
    if (!in_array($domain, ['woocommerce', 'woocommerce-gateway-stripe', 'bootscore'], true)) return $translation;
    static $map = null;
    if ($map === null) {
        $map = [
            // bootscore (theme breadcrumb)
            'Home'                                   => 'Início',
            'Return to shop'                        => 'Voltar para a loja',
            'Calculate shipping'                    => 'Calcular envio',
            'Postcode / ZIP:'                       => 'Consultar CEP',
            'Update cart'                           => 'Atualizar carrinho',
            'Apply coupon'                          => 'Aplicar cupom',
            'Proceed to checkout'                   => 'Finalizar compra',
            'Place order'                           => 'Finalizar pedido',
            'Order notes'                           => 'Observações do pedido',
            'Ship to a different address?'          => 'Entregar em outro endereço?',
            'Billing details'                       => 'Dados de cobrança',
            'Your order'                            => 'Seu pedido',
            'Have a coupon?'                        => 'Tem um cupom?',
            'Click here to enter your coupon code'  => 'Clique aqui para inserir seu cupom',
            'Cart totals'                           => 'Total do carrinho',
            'Free!'                                 => 'Grátis!',
            // woocommerce-gateway-stripe
            '%1$sTest mode:%2$s use the test VISA card 4242424242424242 with any expiry date and CVC. Other payment methods may redirect to a Stripe test page to authorize payment. More test card numbers are listed %3$shere%4$s.'
                => '%1$sModo de teste:%2$s use o cartão VISA de teste 4242424242424242 com qualquer data de validade e CVC. Outros métodos de pagamento podem redirecionar para uma página de teste da Stripe para autorizar o pagamento. Mais números de cartão de teste estão listados %3$saqui%4$s.',
        ];
    }
    return $map[$text] ?? $translation;
}, 10, 3);

// Remove the "Order notes" field from checkout (classic checkout).
add_filter('woocommerce_checkout_fields', function ($fields) {
    unset($fields['order']['order_comments']);
    return $fields;
});

// Rename the shipping package heading ("Remessa 1") shown above the
// shipping method options in cart/checkout order review.
add_filter('woocommerce_shipping_package_name', function () {
    return __('Forma de Entrega', 'woocommerce');
});

// Checkout order review: prepend each line item's thumbnail (with a
// quantity badge) to the product name. review-order.php has no image
// column by default — only product-name/product-total — so the mobile
// summary needs this to show a photo per item like the cart does.
add_filter('woocommerce_cart_item_name', function ($name, $cart_item, $cart_item_key) {
    if (!is_checkout()) {
        return $name;
    }

    $product = $cart_item['data'];
    if (!$product) {
        return $name;
    }

    $thumbnail = $product->get_image('woocommerce_thumbnail');
    $qty       = absint($cart_item['quantity']);

    return sprintf(
        '<span class="headshop-checkout-item"><span class="headshop-checkout-item__thumb">%s<span class="headshop-checkout-item__qty">%d</span></span><span class="headshop-checkout-item__name">%s</span></span>',
        $thumbnail,
        $qty,
        $name
    );
}, 10, 3);

// Force pt-BR for WooCommerce Cart/Checkout block strings (React/JS i18n).
// These render client-side via wp.i18n, not PHP gettext, and the bundled
// pt_BR language pack for these blocks is missing/mismatched for this
// WooCommerce version, so the gettext filter above can't reach them.
add_filter('pre_load_script_translations', function ($translations, $file, $handle, $domain) {
    if ($domain !== 'woocommerce') return $translations;

    $overrides = [
        'Estimated total'                         => 'Total estimado',
        'Shipping will be calculated at checkout' => 'O frete será calculado na finalização da compra',
        'Proceed to Checkout'                     => 'Finalizar compra',
        // Checkout block
        'Order summary'                                        => 'Resumo do pedido',
        'Contact information'                                  => 'Informações de contato',
        'Contact Information'                                  => 'Informações de contato',
        'You are currently checking out as a guest.'           => 'Você está finalizando a compra como visitante.',
        'Shipping address'                                     => 'Endereço de entrega',
        'Use same address for billing'                         => 'Usar o mesmo endereço para cobrança',
        'Shipping options'                                     => 'Opções de entrega',
        'Payment options'                                      => 'Opções de pagamento',
        'Add a note to your order'                             => 'Adicionar uma observação ao pedido',
        'Terms and Conditions'                                 => 'Termos e Condições',
        'Privacy Policy'                                       => 'Política de Privacidade',
        'By proceeding with your purchase you agree to our %1$s and %2$s' => 'Ao continuar com sua compra, você concorda com nossos %1$s e nossa %2$s',
        'Return to Cart'                                       => 'Voltar para o carrinho',
        'Place Order'                                          => 'Finalizar pedido',
        // Coupons, totals, loading states, validation & error messages
        ' Express Checkout'                                    => ' Checkout Expresso',
        '%1$d items in cart'                                   => '%1$d itens no carrinho',
        '%1$d items in cart, total price of %2$s'              => '%1$d itens no carrinho, preço total de %2$s',
        '%1$s (%2$d unit)'                                     => '%1$s (%2$d unidade)',
        '%1$s (%2$d units)'                                    => '%1$s (%2$d unidades)',
        '%1$s ending in %2$s (expires %3$s)'                   => '%1$s terminando em %2$s (expira em %3$s)',
        '%1$s must match the pattern %2$s'                     => '%1$s deve corresponder ao padrão %2$s',
        '%d item'                                               => '%d item',
        '%d items'                                              => '%d itens',
        '%d shipping option was found'                         => '%d opção de entrega encontrada',
        '%d shipping option was found.'                        => '%d opção de entrega encontrada.',
        '%d shipping options were found'                       => '%d opções de entrega encontradas',
        '%d shipping options were found.'                      => '%d opções de entrega encontradas.',
        '%s is invalid'                                        => '%s é inválido',
        '(%s customer reviews)'                                => '(%s avaliações de clientes)',
        '+ Add %s'                                              => '+ Adicionar %s',
        '<price/> x <packageCount/> packages'                  => '<price/> x <packageCount/> pacotes',
        'Add coupons'                                           => 'Adicionar cupons',
        'Applying coupon…'                                     => 'Aplicando cupom…',
        'Checkout error'                                        => 'Erro no checkout',
        "Checkout is not available whilst your cart is empty—please take a look through our store and come back when you're ready to place an order."
            => 'O checkout não está disponível enquanto seu carrinho estiver vazio — dê uma olhada em nossa loja e volte quando estiver pronto para fazer o pedido.',
        'Click here to log in.'                                => 'Clique aqui para entrar.',
        'Coupon code "%s" has been applied to your cart.'      => 'O cupom "%s" foi aplicado ao seu carrinho.',
        'Coupon code "%s" has been removed from your cart.'    => 'O cupom "%s" foi removido do seu carrinho.',
        'Edit your cart'                                        => 'Editar seu carrinho',
        'Enter code'                                            => 'Digite o código',
        'Finish checkout'                                       => 'Finalizar checkout',
        'Including'                                             => 'Incluindo',
        'Including %s'                                          => 'Incluindo %s',
        'Including <TaxAmount/> in taxes'                      => 'Incluindo <TaxAmount/> em impostos',
        'Loading express payment area…'                        => 'Carregando área de pagamento expresso…',
        'Loading express payment method…'                      => 'Carregando método de pagamento expresso…',
        'Loading payment options… '                            => 'Carregando opções de pagamento… ',
        'Loading price… '                                      => 'Carregando preço… ',
        'Loading products in cart…'                            => 'Carregando produtos no carrinho…',
        'Loading shipping options…'                            => 'Carregando opções de entrega…',
        'Loading shipping rates…'                              => 'Carregando taxas de entrega…',
        'Multiple shipments must have the same pickup location' => 'Múltiplos envios devem ter o mesmo local de retirada',
        'No registered Payment Methods'                        => 'Nenhum método de pagamento registrado',
        'Only express payment methods are available for this order. Please select one to continue.'
            => 'Apenas métodos de pagamento expresso estão disponíveis para este pedido. Selecione um para continuar.',
        'Or continue below'                                     => 'Ou continue abaixo',
        'Other available payment methods'                      => 'Outros métodos de pagamento disponíveis',
        'Please edit your cart and try again.'                 => 'Edite seu carrinho e tente novamente.',
        'Please enter a valid postcode'                        => 'Digite um CEP válido',
        'Please fix the following errors before continuing'    => 'Corrija os seguintes erros antes de continuar',
        'Please select a %s'                                   => 'Selecione um %s',
        'Please select a valid option'                         => 'Selecione uma opção válida',
        'Please select your country'                           => 'Selecione seu país',
        'Processing express checkout'                          => 'Processando checkout expresso',
        'Rated %1$s out of 5 based on %2$s customer ratings'   => 'Avaliado em %1$s de 5 com base em %2$s avaliações de clientes',
        'Remove "%s"'                                           => 'Remover "%s"',
        'Remove coupon "%s"'                                    => 'Remover cupom "%s"',
        'Removing coupon…'                                     => 'Removendo cupom…',
        'Save payment information to my account for future purchases.' => 'Salvar informações de pagamento na minha conta para compras futuras.',
        'Saved token for %s'                                   => 'Token salvo para %s',
        'Select a %s'                                           => 'Selecione um %s',
        'Shipping option searched for %d package.'             => 'Opção de entrega pesquisada para %d pacote.',
        'Shipping options searched for %d packages.'           => 'Opções de entrega pesquisadas para %d pacotes.',
        'Shopping cart.'                                        => 'Carrinho de compras.',
        'Show %s more'                                          => 'Mostrar mais %s',
        'Show %s more option'                                  => 'Mostrar mais %s opção',
        'Show %s more options'                                 => 'Mostrar mais %s opções',
        'Show less options'                                     => 'Mostrar menos opções',
        "Something went wrong when placing the order. Check your account's order history or your email for order updates before retrying."
            => 'Algo deu errado ao finalizar o pedido. Verifique o histórico de pedidos da sua conta ou seu e-mail para atualizações antes de tentar novamente.',
        'Something went wrong when placing the order. Check your email for order updates before retrying.'
            => 'Algo deu errado ao finalizar o pedido. Verifique seu e-mail para atualizações antes de tentar novamente.',
        'Sorry, we do not allow orders from the selected country' => 'Desculpe, não aceitamos pedidos do país selecionado',
        'Sorry, we do not ship orders to the selected country'  => 'Desculpe, não enviamos pedidos para o país selecionado',
        'Taxes:'                                                => 'Impostos:',
        'The checkout has encountered an unexpected error. <button>Try reloading the page</button>. If the error persists, please get in touch with us so we can assist.'
            => 'O checkout encontrou um erro inesperado. <button>Tente recarregar a página</button>. Se o erro persistir, entre em contato conosco para que possamos ajudar.',
        'There are no payment methods available. Please contact us for help placing your order.'
            => 'Não há métodos de pagamento disponíveis. Entre em contato conosco para ajudar a finalizar seu pedido.',
        'There is a problem with your cart'                    => 'Há um problema com seu carrinho',
        'There was a problem checking out. Please try again. If the problem persists, please get in touch with us so we can assist.'
            => 'Houve um problema ao finalizar a compra. Tente novamente. Se o problema persistir, entre em contato conosco para que possamos ajudar.',
        'There was a problem with your payment option.'       => 'Houve um problema com sua opção de pagamento.',
        'There was a problem with your shipping option.'      => 'Houve um problema com sua opção de entrega.',
        "There was an error with this payment method. Please verify it's configured correctly."
            => 'Houve um erro com este método de pagamento. Verifique se ele está configurado corretamente.',
        'Total price for <quantity/> <productName/> item: <price/>'  => 'Preço total para <quantity/> item de <productName/>: <price/>',
        'Total price for <quantity/> <productName/> items: <price/>' => 'Preço total para <quantity/> itens de <productName/>: <price/>',
        'Totals will be recalculated when a valid shipping method is selected.' => 'Os totais serão recalculados quando um método de entrega válido for selecionado.',
        'Use another payment method.'                          => 'Usar outro método de pagamento.',
        'We are experiencing difficulties with this payment method. Please contact us for assistance.'
            => 'Estamos com dificuldades com este método de pagamento. Entre em contato conosco para obter assistência.',
        // Shipping/delivery, totals, cart line items, addresses, account, reviews, errors
        '"%s" was removed from your cart.'                     => '"%s" foi removido do seu carrinho.',
        '%1$d item in cart'                                    => '%1$d item no carrinho',
        '%1$d item in cart, total price of %2$s'               => '%1$d item no carrinho, preço total de %2$s',
        '%1$s ending in %2$s'                                  => '%1$s terminando em %2$s',
        '%d in cart'                                           => '%d no carrinho',
        '%s (optional)'                                        => '%s (opcional)',
        '%s has been removed from your cart.'                  => '%s foi removido do seu carrinho.',
        '(%s customer review)'                                 => '(%s avaliação de cliente)',
        '<price/> x <packageCount/> package'                   => '<price/> x <packageCount/> pacote',
        'Add to cart'                                           => 'Adicionar ao carrinho',
        'Additional order information'                         => 'Informações adicionais do pedido',
        'Apply'                                                 => 'Aplicar',
        'Available on backorder'                               => 'Disponível sob encomenda',
        'Billing address'                                       => 'Endereço de cobrança',
        'Billing and shipping address'                         => 'Endereço de cobrança e entrega',
        'Browse store'                                          => 'Explorar loja',
        'Buy product'                                           => 'Comprar produto',
        'Calculated at checkout'                               => 'Calculado no checkout',
        'Cart'                                                  => 'Carrinho',
        'Checkout'                                              => 'Checkout',
        'Close'                                                 => 'Fechar',
        'Color'                                                 => 'Cor',
        'Coupon: %s'                                            => 'Cupom: %s',
        'Coupons'                                               => 'Cupons',
        'Create a password'                                    => 'Criar uma senha',
        'Create an account with %s'                            => 'Criar uma conta com %s',
        'Delivery'                                              => 'Entrega',
        'Details'                                               => 'Detalhes',
        'Discount'                                              => 'Desconto',
        'Discount:'                                             => 'Desconto:',
        'Discounted price:'                                    => 'Preço com desconto:',
        'Dismiss this notice'                                  => 'Dispensar este aviso',
        'Edit'                                                  => 'Editar',
        'Edit billing address'                                 => 'Editar endereço de cobrança',
        'Edit shipping address'                                => 'Editar endereço de entrega',
        'Enter a shipping address to view shipping options.'  => 'Digite um endereço de entrega para ver as opções de frete.',
        'Enter address to calculate'                           => 'Digite o endereço para calcular',
        'Enter the billing and shipping address that matches your payment method.'
            => 'Digite o endereço de cobrança e entrega que corresponde ao seu método de pagamento.',
        'Error:'                                                => 'Erro:',
        'Fee'                                                   => 'Taxa',
        'Fees:'                                                 => 'Taxas:',
        'Flat rate shipping'                                   => 'Frete com taxa fixa',
        'Free'                                                  => 'Grátis',
        'Free shipping'                                         => 'Frete grátis',
        'Increase quantity of %s'                              => 'Aumentar quantidade de %s',
        'Link to %s'                                            => 'Link para %s',
        'Loading…'                                             => 'Carregando…',
        'Local pickup'                                          => 'Retirada local',
        'Log in'                                                => 'Entrar',
        'No Reviews'                                            => 'Sem avaliações',
        'No available delivery option'                         => 'Nenhuma opção de entrega disponível',
        'No shipping options are available for this address. Please verify the address is correct or try a different address.'
            => 'Não há opções de entrega disponíveis para este endereço. Verifique se o endereço está correto ou tente outro endereço.',
        'Notes about your order, e.g. special notes for delivery.' => 'Observações sobre seu pedido, ex.: observações especiais sobre entrega.',
        'Notes about your order.'                              => 'Observações sobre seu pedido.',
        'Oops!'                                                 => 'Ops!',
        'Or'                                                    => 'Ou',
        'Password strength'                                    => 'Força da senha',
        'Password strength: %1$s (%2$d characters long)'      => 'Força da senha: %1$s (%2$d caracteres)',
        'Pickup'                                                => 'Retirada',
        'Pickup locations'                                     => 'Locais de retirada',
        'Please check this box if you want to proceed.'       => 'Marque esta caixa se quiser continuar.',
        'Please create a stronger password'                   => 'Crie uma senha mais forte',
        'Please enter a valid %s'                              => 'Digite um %s válido',
        'Please enter a valid email address'                  => 'Digite um endereço de e-mail válido',
        'Please enter a valid password'                       => 'Digite uma senha válida',
        'Please read and accept the terms and conditions.'   => 'Leia e aceite os termos e condições.',
        'Previous price:'                                      => 'Preço anterior:',
        'Price between %1$s and %2$s'                          => 'Preço entre %1$s e %2$s',
        'Product'                                               => 'Produto',
        'Product on sale'                                       => 'Produto em promoção',
        'Products in cart'                                      => 'Produtos no carrinho',
        'Quantity increased to %s.'                            => 'Quantidade aumentada para %s.',
        'Quantity of %s in your cart.'                         => 'Quantidade de %s no seu carrinho.',
        'Quantity reduced to %s.'                              => 'Quantidade reduzida para %s.',
        'Rated %1$s out of 5 based on %2$s customer rating'   => 'Avaliado em %1$s de 5 com base em %2$s avaliação de cliente',
        'Rated %f out of 5'                                    => 'Avaliado em %f de 5',
        'Read less'                                             => 'Ler menos',
        'Read more'                                             => 'Ler mais',
        'Reduce quantity of %s'                                => 'Reduzir quantidade de %s',
        'Reload the page'                                       => 'Recarregar a página',
        'Remove'                                                => 'Remover',
        'Remove %s from cart'                                  => 'Remover %s do carrinho',
        'Retry'                                                 => 'Tentar novamente',
        'Sale'                                                  => 'Promoção',
        'Save %s'                                               => 'Economize %s',
        'Ship'                                                  => 'Enviar',
        'Shipping'                                              => 'Frete',
        'Shipping options are not available'                   => 'Opções de entrega não disponíveis',
        'Shipping:'                                             => 'Frete:',
        'Show less'                                             => 'Mostrar menos',
        'Size'                                                  => 'Tamanho',
        'Something went wrong. Please contact us for assistance.' => 'Algo deu errado. Entre em contato conosco para obter assistência.',
        'Something went wrong. Please contact us to get assistance.' => 'Algo deu errado. Entre em contato conosco para obter assistência.',
        'Sorry, this order requires a shipping option.'       => 'Desculpe, este pedido requer uma opção de entrega.',
        'Step'                                                  => 'Etapa',
        'Strong'                                                => 'Forte',
        'Subtotal'                                              => 'Subtotal',
        'Subtotal:'                                             => 'Subtotal:',
        'Taxes'                                                 => 'Impostos',
        'The cart has encountered an unexpected error. If the error persists, please get in touch with us for help.'
            => 'O carrinho encontrou um erro inesperado. Se o erro persistir, entre em contato conosco para obter ajuda.',
        'The quantity of "%1$s" was changed to %2$s.'         => 'A quantidade de "%1$s" foi alterada para %2$s.',
        'The response is not a valid JSON response.'          => 'A resposta não é uma resposta JSON válida.',
        'There was an error loading the content.'             => 'Houve um erro ao carregar o conteúdo.',
        'Too weak'                                              => 'Muito fraca',
        'Total'                                                 => 'Total',
        'Unable to get cart data from the API.'               => 'Não foi possível obter os dados do carrinho da API.',
        'Very strong'                                           => 'Muito forte',
        'Weak'                                                  => 'Fraca',
        'You must accept our %1$s and %2$s to continue with your purchase.'
            => 'Você deve aceitar nossos %1$s e nossa %2$s para continuar com sua compra.',
        'You must be logged in to checkout.'                  => 'Você precisa estar conectado para finalizar a compra.',
        'Your cart is currently empty!'                        => 'Seu carrinho está vazio no momento!',
        'calculated with an address'                           => 'calculado com um endereço',
        'free'                                                  => 'grátis',
        'from <price />'                                        => 'a partir de <price />',
    ];

    $data = ($file && is_readable($file)) ? json_decode(file_get_contents($file), true) : null;
    if (!is_array($data)) {
        $data = [
            'domain'      => 'messages',
            'locale_data' => [
                'messages' => [
                    '' => [
                        'domain'       => 'messages',
                        'lang'         => 'pt_BR',
                        'plural-forms' => 'nplurals=2; plural=(n > 1);',
                    ],
                ],
            ],
        ];
    }

    foreach ($overrides as $text => $translation) {
        $data['locale_data']['messages'][$text] = [$translation];
    }

    return wp_json_encode($data);
}, 10, 4);


/* =====================================================================
   2. REGISTER ADDITIONAL NAV MENUS
   ===================================================================== */

add_action('after_setup_theme', 'headshop_register_nav_menus');
function headshop_register_nav_menus() {
    register_nav_menu('menu-bars', 'Menu Bars (fullscreen overlay)');
}


/* =====================================================================
   3. HIDE ADMIN BAR ON FRONT PAGE
   ===================================================================== */

// Oculta a admin bar na parte pública para usuários logados (exceto admin)
add_filter('show_admin_bar', function ($show) {
    if (!is_admin()) {
        return false;
    }
    return $show;
});


/* =====================================================================
   3. DISABLE BOOTSCORE SKIP-CART REDIRECT
   ===================================================================== */

add_filter('bootscore/skip_cart', '__return_false');


/* =====================================================================
   4. MY ACCOUNT / CHECKOUT — REMOVE SIDEBAR
   ===================================================================== */

add_filter('sidebars_widgets', function ($sidebars_widgets) {
    if (is_account_page() || is_checkout()) {
        $sidebars_widgets['sidebar-1'] = [];
    }
    return $sidebars_widgets;
});


/* =====================================================================
   5. BANNER CPT
   ===================================================================== */

add_action('init', 'headshop_register_banner_cpt');
function headshop_register_banner_cpt() {
    register_post_type('banner', array(
        'labels' => array(
            'name'               => 'Banners',
            'singular_name'      => 'Banner',
            'menu_name'          => 'Banners',
            'add_new'            => 'Adicionar Novo',
            'add_new_item'       => 'Adicionar Novo Banner',
            'edit_item'          => 'Editar Banner',
            'view_item'          => 'Ver Banner',
            'all_items'          => 'Todos os Banners',
            'search_items'       => 'Pesquisar Banners',
            'not_found'          => 'Nenhum banner encontrado.',
            'not_found_in_trash' => 'Nenhum banner encontrado na lixeira.',
        ),
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-images-alt2',
        'supports'           => array('title', 'thumbnail'),
    ));
}


/* =====================================================================
   5a. BANNER — METABOX IMAGEM MOBILE
   ===================================================================== */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'banner_mobile_image',
        'Imagem Mobile',
        'headshop_banner_mobile_metabox',
        'banner',
        'side',
        'default'
    );
    add_meta_box(
        'banner_text_overlay',
        'Texto sobre a imagem',
        'headshop_banner_text_metabox',
        'banner',
        'normal',
        'high'
    );
});

function headshop_banner_text_metabox($post) {
    $title    = get_post_meta($post->ID, '_banner_text_title', true);
    $subtitle = get_post_meta($post->ID, '_banner_text_subtitle', true);
    wp_nonce_field('headshop_banner_text_nonce', 'headshop_banner_text_nonce');

    $editor_settings = array(
        'media_buttons' => false,
        'quicktags'     => false,
        'tinymce'       => array(
            'toolbar1'       => 'fontsizeselect,|,bold,italic,underline,strikethrough,|,alignleft,aligncenter,alignright,|,removeformat',
            'toolbar2'       => '',
            'fontsize_formats' => '12px 14px 16px 18px 20px 24px 28px 32px 40px 48px 56px 64px',
        ),
    );
    ?>
    <p style="font-weight:600;margin-bottom:4px;">Título</p>
    <?php wp_editor($title, 'banner_text_title', array_merge($editor_settings, array(
        'textarea_name' => 'banner_text_title',
        'textarea_rows' => 3,
    ))); ?>

    <p style="font-weight:600;margin:16px 0 4px;">Subtítulo</p>
    <?php wp_editor($subtitle, 'banner_text_subtitle', array_merge($editor_settings, array(
        'textarea_name' => 'banner_text_subtitle',
        'textarea_rows' => 3,
    ))); ?>
    <p class="description" style="margin-top:8px;">Deixe em branco para não exibir texto sobre o banner.</p>
    <?php
}

function headshop_banner_mobile_metabox($post) {
    $mobile_id  = (int) get_post_meta($post->ID, '_banner_mobile_image', true);
    $mobile_url = $mobile_id ? wp_get_attachment_image_url($mobile_id, 'medium') : '';
    wp_nonce_field('headshop_banner_mobile_nonce', 'headshop_banner_mobile_nonce');
    ?>
    <div id="headshop-mobile-wrap">
      <?php if ($mobile_url) : ?>
        <img id="headshop-mobile-preview" src="<?= esc_url($mobile_url); ?>"
             style="max-width:100%;height:auto;display:block;margin-bottom:8px;" />
      <?php else : ?>
        <img id="headshop-mobile-preview" src="" style="max-width:100%;height:auto;display:none;margin-bottom:8px;" />
      <?php endif; ?>
      <input type="hidden" id="headshop_banner_mobile_id" name="headshop_banner_mobile_id"
             value="<?= esc_attr($mobile_id ?: ''); ?>" />
      <button type="button" id="headshop-mobile-select" class="button button-secondary" style="width:100%;">
        <?= $mobile_id ? 'Trocar imagem mobile' : 'Selecionar imagem mobile'; ?>
      </button>
      <button type="button" id="headshop-mobile-remove" class="button"
              style="width:100%;margin-top:4px;color:#b32d2e;<?= $mobile_id ? '' : 'display:none;'; ?>">
        Remover
      </button>
      <p class="description" style="margin-top:8px;font-size:11px;">
        Se não definida, usa a imagem desktop.
      </p>
    </div>
    <script>
    jQuery(function ($) {
        var frame;
        var $preview = $('#headshop-mobile-preview');
        var $input   = $('#headshop_banner_mobile_id');
        var $select  = $('#headshop-mobile-select');
        var $remove  = $('#headshop-mobile-remove');

        $select.on('click', function () {
            if (frame) { frame.open(); return; }
            frame = wp.media({
                title: 'Selecionar imagem mobile',
                multiple: false,
                library: { type: 'image' },
                button:  { text: 'Usar esta imagem' }
            });
            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                $input.val(att.id);
                $preview.attr('src', att.url).show();
                $select.text('Trocar imagem mobile');
                $remove.show();
            });
            frame.open();
        });

        $remove.on('click', function () {
            $input.val('');
            $preview.attr('src', '').hide();
            $select.text('Selecionar imagem mobile');
            $remove.hide();
        });
    });
    </script>
    <?php
}

add_action('save_post_banner', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Mobile image
    if (isset($_POST['headshop_banner_mobile_nonce']) &&
        wp_verify_nonce($_POST['headshop_banner_mobile_nonce'], 'headshop_banner_mobile_nonce')) {
        $img_id = absint($_POST['headshop_banner_mobile_id'] ?? 0);
        if ($img_id) {
            update_post_meta($post_id, '_banner_mobile_image', $img_id);
        } else {
            delete_post_meta($post_id, '_banner_mobile_image');
        }
    }

    // Text overlay
    if (isset($_POST['headshop_banner_text_nonce']) &&
        wp_verify_nonce($_POST['headshop_banner_text_nonce'], 'headshop_banner_text_nonce')) {
        $title    = wp_kses_post(wp_unslash($_POST['banner_text_title'] ?? ''));
        $subtitle = wp_kses_post(wp_unslash($_POST['banner_text_subtitle'] ?? ''));
        update_post_meta($post_id, '_banner_text_title', $title);
        update_post_meta($post_id, '_banner_text_subtitle', $subtitle);
    }
});

// Enqueue wp.media no admin do banner
add_action('admin_enqueue_scripts', function ($hook) {
    global $post;
    if (($hook === 'post-new.php' || $hook === 'post.php') &&
        isset($post) && $post->post_type === 'banner') {
        wp_enqueue_media();
    }
});


/* =====================================================================
   5. BANNER SLIDER (Bootstrap Carousel)
   ===================================================================== */

function headshop_banner_slider() {
    if (!is_front_page()) return;

    $banners = new WP_Query(array(
        'post_type'      => 'banner',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ));

    if (!$banners->have_posts()) return;

    $slides = array();
    while ($banners->have_posts()) {
        $banners->the_post();
        $desktop_id  = get_post_thumbnail_id();
        $desktop_url = $desktop_id ? wp_get_attachment_image_url($desktop_id, 'full') : '';
        if (!$desktop_url) continue;

        $mobile_id  = (int) get_post_meta(get_the_ID(), '_banner_mobile_image', true);
        $mobile_url = $mobile_id ? wp_get_attachment_image_url($mobile_id, 'full') : $desktop_url;

        $slides[] = array(
            'desktop'  => $desktop_url,
            'mobile'   => $mobile_url,
            'title'    => get_post_meta(get_the_ID(), '_banner_text_title', true),
            'subtitle' => get_post_meta(get_the_ID(), '_banner_text_subtitle', true),
        );
    }
    wp_reset_postdata();

    if (empty($slides)) return;
    ?>
    <div id="bannerCarousel" class="carousel slide headshop-banner" data-bs-ride="carousel" data-bs-interval="5000">
      <!-- Indicators -->
      <div class="carousel-indicators">
        <?php foreach ($slides as $i => $slide) : ?>
          <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="<?= $i; ?>"<?php if ($i === 0) echo ' class="active" aria-current="true"'; ?> aria-label="Slide <?= $i + 1; ?>"></button>
        <?php endforeach; ?>
      </div>

      <!-- Slides -->
      <div class="carousel-inner h-100">
        <?php foreach ($slides as $i => $slide) : ?>
          <div class="carousel-item h-100<?php if ($i === 0) echo ' active'; ?>">
            <div class="headshop-banner__slide"
                 style="--img-desktop:url('<?= esc_url($slide['desktop']); ?>');--img-mobile:url('<?= esc_url($slide['mobile']); ?>');">
              <?php if (!empty($slide['title'])) : ?>
              <div class="headshop-banner__caption">
                <div class="container">
                  <div class="headshop-banner__caption-title"><?= wp_kses_post(wpautop($slide['title'])); ?></div>
                  <?php if (!empty($slide['subtitle'])) : ?>
                  <div class="headshop-banner__caption-sub"><?= wp_kses_post(wpautop($slide['subtitle'])); ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
        <span class="headshop-banner__nav-icon" aria-hidden="true">&#8249;</span>
        <span class="visually-hidden">Anterior</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
        <span class="headshop-banner__nav-icon" aria-hidden="true">&#8250;</span>
        <span class="visually-hidden">Próximo</span>
      </button>
    </div>
    <?php
}


/* =====================================================================
   5. ADMIN — HOME CONFIG (Categories)
   ===================================================================== */

add_action('admin_menu', 'headshop_register_settings');
function headshop_register_settings() {
    add_menu_page(
        'Configurações da Home',
        'Home Config',
        'manage_options',
        'headshop-home-config',
        'headshop_home_config_page',
        'dashicons-admin-home',
        61
    );
}

function headshop_home_config_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['headshop_cats_nonce']) && wp_verify_nonce($_POST['headshop_cats_nonce'], 'save_home_categories')) {
        $selected = isset($_POST['home_categories_ordered']) ? $_POST['home_categories_ordered'] : '';
        $ordered  = array_filter(array_map('intval', explode(',', $selected)));
        update_option('headshop_home_categories', $ordered);
        echo '<div class="notice notice-success is-dismissible"><p>Configurações salvas com sucesso!</p></div>';
    }

    $saved_cats     = get_option('headshop_home_categories', array());
    // Fallback: read old option key from wp-headshop
    if (empty($saved_cats)) {
        $saved_cats = get_option('storefront_child_home_categories', array());
    }
    $all_categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));

    // Sort hierarchically
    $sorted    = array();
    $by_parent = array();
    if (!is_wp_error($all_categories)) {
        foreach ($all_categories as $cat) {
            $by_parent[$cat->parent][] = $cat;
        }
    }
    $add_children = function (&$sorted, $by_parent, $pid = 0, $lvl = 0) use (&$add_children) {
        if (!isset($by_parent[$pid])) return;
        foreach ($by_parent[$pid] as $c) {
            $c->level = $lvl;
            $sorted[] = $c;
            $add_children($sorted, $by_parent, $c->term_id, $lvl + 1);
        }
    };
    $add_children($sorted, $by_parent);
    ?>
    <div class="wrap">
        <h1>Configurações da Home</h1>
        <div style="max-width:1200px">
            <form method="post">
                <?php wp_nonce_field('save_home_categories', 'headshop_cats_nonce'); ?>
                <input type="hidden" name="home_categories_ordered" id="homeCategoriesOrdered" value="<?= esc_attr(implode(',', $saved_cats)); ?>" />

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:20px 0;">
                    <div>
                        <h2>Categorias Disponíveis</h2>
                        <p>Arraste as categorias para a direita para exibir na home:</p>
                        <div id="availableCategories" class="category-sortable-list" style="background:#f9f9f9; border:2px dashed #ccc; border-radius:8px; padding:15px; min-height:400px;">
                            <?php foreach ($sorted as $cat) :
                                if (in_array($cat->term_id, $saved_cats)) continue; ?>
                                <div class="category-item" data-id="<?= esc_attr($cat->term_id); ?>" draggable="true" style="background:#fff; border:1px solid #ddd; border-radius:4px; padding:12px; margin-bottom:8px; cursor:move; display:flex; align-items:center; gap:10px;">
                                    <span class="dashicons dashicons-move" style="color:#999;"></span>
                                    <span style="flex:1;">
                                        <?= str_repeat('<span style="color:#ccc;">└</span> ', $cat->level); ?>
                                        <?= esc_html($cat->name); ?>
                                        <small style="color:#999;">(<?= $cat->count; ?>)</small>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <h2>Categorias na Home <span style="font-size:14px; font-weight:normal; color:#999;">(ordem de exibição)</span></h2>
                        <p>Arraste para reordenar ou remova arrastando para a esquerda:</p>
                        <div id="selectedCategories" class="category-sortable-list" style="background:#e8f5e9; border:2px solid #4caf50; border-radius:8px; padding:15px; min-height:400px;">
                            <?php foreach ($saved_cats as $cid) :
                                $cat = get_term($cid, 'product_cat');
                                if (!$cat || is_wp_error($cat)) continue;
                                $lvl = 0; $pid = $cat->parent;
                                while ($pid > 0) { $p = get_term($pid, 'product_cat'); $pid = $p->parent; $lvl++; }
                            ?>
                                <div class="category-item" data-id="<?= esc_attr($cid); ?>" draggable="true" style="background:#fff; border:1px solid #4caf50; border-radius:4px; padding:12px; margin-bottom:8px; cursor:move; display:flex; align-items:center; gap:10px;">
                                    <span class="dashicons dashicons-move" style="color:#4caf50;"></span>
                                    <span style="flex:1;">
                                        <?= str_repeat('<span style="color:#ccc;">└</span> ', $lvl); ?>
                                        <?= esc_html($cat->name); ?>
                                        <small style="color:#999;">(<?= $cat->count; ?>)</small>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php submit_button('Salvar Configurações', 'primary', 'submit', false); ?>
            </form>
        </div>
    </div>

    <style>
        .category-item:hover { box-shadow:0 2px 8px rgba(0,0,0,.1); transform:translateY(-2px); transition:all .2s ease; }
        .category-sortable-list.drag-over { background:#fff3cd !important; border-color:#ffc107 !important; }
        .category-item.dragging { opacity:.5; }
    </style>

    <script>
    (function(){
        var available=document.getElementById('availableCategories');
        var selected=document.getElementById('selectedCategories');
        var input=document.getElementById('homeCategoriesOrdered');
        var dragged=null;

        function setupDD(c){
            c.querySelectorAll('.category-item').forEach(function(el){
                el.addEventListener('dragstart',function(e){dragged=this;this.classList.add('dragging');e.dataTransfer.effectAllowed='move';});
                el.addEventListener('dragend',function(){this.classList.remove('dragging');available.classList.remove('drag-over');selected.classList.remove('drag-over');});
            });
        }

        [available,selected].forEach(function(c){
            c.addEventListener('dragover',function(e){e.preventDefault();e.dataTransfer.dropEffect='move';this.classList.add('drag-over');});
            c.addEventListener('dragleave',function(){this.classList.remove('drag-over');});
            c.addEventListener('drop',function(e){e.preventDefault();this.classList.remove('drag-over');if(dragged){this.appendChild(dragged);update();}});
            setupDD(c);
        });

        function update(){
            var ids=Array.from(selected.querySelectorAll('.category-item')).map(function(el){return el.getAttribute('data-id');});
            input.value=ids.join(',');
        }
    })();
    </script>
    <?php
}


/* =====================================================================
   6. CATEGORIES SECTION
   ===================================================================== */

function headshop_categories_section() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $selected = get_option('headshop_home_categories', array());
    // Fallback to old option
    if (empty($selected)) {
        $selected = get_option('storefront_child_home_categories', array());
    }

    if (empty($selected)) {
        $categories = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
        ));
    } else {
        $categories = get_terms(array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'include'    => $selected,
            'orderby'    => 'include',
        ));
    }

    if (empty($categories) || is_wp_error($categories)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-categories py-5">
      <div class="container" style="max-width:1400px;">
        <div class="headshop-categories__grid">
          <?php foreach ($categories as $cat) :
              $tid  = $cat->term_id;
              $link = get_term_link($tid, 'product_cat');
              $thid = get_term_meta($tid, 'thumbnail_id', true);
              $img  = $thid ? wp_get_attachment_url($thid) : $placeholder;
          ?>
            <a href="<?= esc_url($link); ?>" class="headshop-categories__card">
              <div class="headshop-categories__image" style="background-image: url('<?= esc_url($img); ?>');"></div>
              <h3 class="headshop-categories__name"><?= esc_html($cat->name); ?></h3>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   7. SEARCH OVERLAY
   ===================================================================== */

function headshop_search_overlay() {
    $action = esc_url(home_url('/'));
    $is_wc  = class_exists('WooCommerce');
    ?>
    <div id="headerSearchOverlay" class="headshop-search-overlay" aria-hidden="true">
      <button id="searchOverlayClose" class="headshop-search-overlay__close" type="button" aria-label="Fechar busca">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
        </svg>
      </button>
      <div class="headshop-search-overlay__inner">
        <form role="search" method="get" class="headshop-search-overlay__form" action="<?= $action; ?>">
          <input id="headerSearchInput" class="headshop-search-overlay__input" type="search" name="s" placeholder="Pesquisar" autocomplete="off" />
          <?php if ($is_wc) : ?>
            <input type="hidden" name="post_type" value="product" />
          <?php endif; ?>
          <button class="headshop-search-overlay__submit" type="submit" aria-label="Buscar">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
          </button>
        </form>
      </div>
    </div>
    <?php
}

// Add search overlay to all pages via footer
add_action('wp_footer', function () {
    if (!is_front_page()) {
        headshop_search_overlay();
    }
}, 5);


/* =====================================================================
   8. CART DROPDOWN (AJAX)
   ===================================================================== */

function headshop_render_cart_dropdown() {
    if (!class_exists('WooCommerce') || !WC()->cart) {
        echo '<div class="p-4">Carrinho indisponível.</div>';
        return;
    }

    $items = WC()->cart->get_cart();
    if (empty($items)) {
        echo '<div class="p-4 text-center">Seu carrinho está vazio.</div>';
        return;
    }

    $saved_total  = 0.0;
    $cart_url     = wc_get_cart_url();
    $checkout_url = wc_get_checkout_url();

    echo '<div class="headshop-cart__layout">';
    echo '  <div class="headshop-cart__body p-3">';

    foreach ($items as $key => $item) {
        $product = $item['data'] ?? null;
        if (!$product || !$product->exists()) continue;

        $name = $product->get_name();
        $qty  = (int)($item['quantity'] ?? 1);

        $thumb_src = '';
        $thumb_id  = $product->get_image_id();
        if ($thumb_id) $thumb_src = wp_get_attachment_image_url($thumb_id, 'thumbnail') ?: '';
        if (!$thumb_src) $thumb_src = wc_placeholder_img_src('thumbnail');

        $price_now     = (float)$product->get_price();
        $price_regular = (float)$product->get_regular_price();
        $line_now      = $price_now * $qty;
        $line_regular  = ($price_regular > 0 ? $price_regular : $price_now) * $qty;
        $saved_total  += max(0, $line_regular - $line_now);

        echo '<div class="d-flex gap-3 align-items-start py-3 border-bottom">';
        echo '  <img class="rounded-2 flex-shrink-0 border" src="' . esc_url($thumb_src) . '" alt="" width="56" height="56" loading="lazy" />';
        echo '  <div class="flex-grow-1">';
        echo '    <div class="fw-semibold text-secondary">' . esc_html($name) . '</div>';
        $meta = wc_get_formatted_cart_item_data($item);
        if ($meta) echo '<div class="text-muted small">' . wp_kses_post($meta) . '</div>';
        echo '    <div class="mt-2">';
        echo '      <span class="headshop-cart__price fw-semibold">' . wp_kses_post(wc_price($line_now)) . '</span>';
        if ($line_regular > $line_now) {
            echo '    <span class="text-muted text-decoration-line-through ms-2">' . wp_kses_post(wc_price($line_regular)) . '</span>';
        }
        echo '    </div>';
        echo '  </div>';
        echo '  <div class="text-end ms-2">';
        echo '    <div class="small text-muted">' . intval($qty) . '×</div>';
        echo '    <button type="button" class="cart-remove cart-remove--icon" data-cart-item-key="' . esc_attr($key) . '" aria-label="Remover item"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>';
        echo '  </div>';
        echo '</div>';
    }

    echo '  </div>';

    echo '<div class="headshop-cart__footer p-3 border-top">';
    $total = WC()->cart->get_total();
    echo '  <div class="d-flex justify-content-between fw-semibold">';
    echo '    <span>Total</span><span>' . wp_kses_post($total) . '</span>';
    echo '  </div>';

    $coupon = (float)WC()->cart->get_discount_total() + (float)WC()->cart->get_discount_tax();
    $economy = $coupon > 0 ? $coupon : $saved_total;
    if ($economy > 0) {
        echo '<div class="text-muted small mt-1">Você economizou ' . wp_kses_post(wc_price($economy)) . '!</div>';
    }

    echo '  <div class="mt-3 d-grid gap-2">';
    echo '    <a class="btn headshop-cart__action-btn fw-bold text-uppercase" href="' . esc_url($cart_url) . '"><span class="dashicons dashicons-cart"></span> Ver carrinho</a>';
    echo '    <a class="btn headshop-cart__action-btn fw-bold text-uppercase" href="' . esc_url($checkout_url) . '"><span class="dashicons dashicons-lock"></span> Finalizar compra</a>';
    echo '  </div>';
    echo '</div>';
    echo '</div>';
}

// AJAX remove item
function headshop_cart_remove_ajax() {
    check_ajax_referer('headshop_cart');
    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce indisponível'));
    }
    $key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
    if (!$key || !isset(WC()->cart->get_cart()[$key])) {
        wp_send_json_error(array('message' => 'Item inválido'));
    }
    WC()->cart->remove_cart_item($key);
    WC()->cart->calculate_totals();
    ob_start();
    headshop_render_cart_dropdown();
    $html = ob_get_clean();
    wp_send_json_success(array(
        'count' => WC()->cart->get_cart_contents_count(),
        'html'  => $html,
    ));
}
add_action('wp_ajax_headshop_cart_remove', 'headshop_cart_remove_ajax');
add_action('wp_ajax_nopriv_headshop_cart_remove', 'headshop_cart_remove_ajax');

// AJAX refresh dropdown (sync after WC events)
function headshop_cart_refresh_ajax() {
    check_ajax_referer('headshop_cart', '_ajax_nonce');
    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce indisponível'));
    }
    WC()->cart->calculate_totals();
    ob_start();
    headshop_render_cart_dropdown();
    $html = ob_get_clean();
    wp_send_json_success(array(
        'count' => WC()->cart->get_cart_contents_count(),
        'html'  => $html,
    ));
}
add_action('wp_ajax_headshop_cart_refresh',        'headshop_cart_refresh_ajax');
add_action('wp_ajax_nopriv_headshop_cart_refresh', 'headshop_cart_refresh_ajax');

function headshop_ajax_add_to_cart() {
    check_ajax_referer('headshop_cart', '_ajax_nonce');

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $quantity   = isset($_POST['quantity'])   ? absint($_POST['quantity'])   : 1;

    if (!$product_id) {
        wp_send_json_error(['message' => 'Produto inválido.']);
    }

    $key = WC()->cart->add_to_cart($product_id, $quantity);

    if ($key) {
        wp_send_json_success([
            'count'   => WC()->cart->get_cart_contents_count(),
            'message' => esc_html(get_the_title($product_id)) . ' adicionado ao carrinho!',
        ]);
    } else {
        wp_send_json_error(['message' => 'Não foi possível adicionar. Verifique o estoque.']);
    }
}
add_action('wp_ajax_headshop_add_to_cart',        'headshop_ajax_add_to_cart');
add_action('wp_ajax_nopriv_headshop_add_to_cart', 'headshop_ajax_add_to_cart');


/* =====================================================================
   9. RECENT PRODUCTS SECTION
   ===================================================================== */

function headshop_recent_products() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $products = wc_get_products(array(
        'status'  => 'publish',
        'limit'   => 8,
        'orderby' => 'date',
        'order'   => 'DESC',
        'visibility' => 'visible',
    ));

    if (empty($products)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-recent-products py-5">
      <div class="container" style="max-width:1400px;">
        <h2 class="headshop-recent-products__title text-center mb-4">Produtos Recentes</h2>
        <div class="headshop-recent-products__grid">
          <?php foreach ($products as $product) :
              $id        = $product->get_id();
              $name      = $product->get_name();
              $link      = get_permalink($id);
              $img_id    = $product->get_image_id();
              $img_url   = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : $placeholder;
              $price_html = $product->get_price_html();
              $on_sale    = $product->is_on_sale();
          ?>
            <a href="<?= esc_url($link); ?>" class="headshop-recent-products__card">
              <?php if ($on_sale) : ?>
                <span class="headshop-recent-products__badge">Oferta</span>
              <?php endif; ?>
              <div class="headshop-recent-products__image-wrap">
                <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($name); ?>" class="headshop-recent-products__image" loading="lazy" />
              </div>
              <div class="headshop-recent-products__info">
                <h3 class="headshop-recent-products__name"><?= esc_html($name); ?></h3>
                <div class="headshop-recent-products__price"><?= wp_kses_post($price_html); ?></div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
          <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn headshop-recent-products__btn">Ver todos os produtos</a>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   10. SALE PRODUCTS SECTION
   ===================================================================== */

function headshop_sale_products() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $on_sale_ids = wc_get_product_ids_on_sale();
    if (empty($on_sale_ids)) return;

    $products = wc_get_products(array(
        'status'     => 'publish',
        'limit'      => 8,
        'orderby'    => 'rand',
        'visibility' => 'visible',
        'include'    => $on_sale_ids,
    ));

    if (empty($products)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-sale-products py-5">
      <div class="container" style="max-width:1400px;">
        <div class="headshop-section-title">
          <span class="headshop-section-title__eyebrow">— Destaques da semana</span>
          <div class="headshop-section-title__row">
            <h2 class="headshop-section-title__text">EM OFERTA</h2>
            <div class="headshop-section-title__rule"></div>
          </div>
        </div>
        <div class="headshop-products-carousel">
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--prev" aria-label="Anterior">&#8592;</button>
          <div class="headshop-sale-products__grid headshop-products-carousel__track">
            <?php foreach ($products as $product) :
                $id         = $product->get_id();
                $name       = $product->get_name();
                $link       = get_permalink($id);
                $img_id     = $product->get_image_id();
                $img_url    = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : $placeholder;
                $price_html = $product->get_price_html();
                $regular    = (float) $product->get_regular_price();
                $sale       = (float) $product->get_sale_price();
                $discount   = $regular > 0 ? round((1 - $sale / $regular) * 100) : 0;
            ?>
              <div class="headshop-sale-products__card">
                <?php if ($discount > 0) : ?>
                  <span class="headshop-sale-products__badge">-<?= (int) $discount; ?>%</span>
                <?php else : ?>
                  <span class="headshop-sale-products__badge">OFERTA</span>
                <?php endif; ?>
                <a href="<?= esc_url($link); ?>" class="headshop-sale-products__image-link">
                  <div class="headshop-sale-products__image-wrap">
                    <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($name); ?>" class="headshop-sale-products__image" loading="lazy" />
                  </div>
                </a>
                <div class="headshop-sale-products__info">
                  <h3 class="headshop-sale-products__name"><?= esc_html($name); ?></h3>
                  <div class="headshop-sale-products__price"><?= wp_kses_post($price_html); ?></div>
                  <a href="<?= esc_url($product->add_to_cart_url()); ?>"
                     class="headshop-carousel__add-btn"
                     data-product_id="<?= esc_attr($id); ?>"
                     data-quantity="1"
                     rel="nofollow">Adicionar</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--next" aria-label="Próximo">&#8594;</button>
        </div>
        <div class="text-center mt-4">
          <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn headshop-sale-products__btn">Ver todos →</a>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   11. NEW PRODUCTS SECTION
   ===================================================================== */

function headshop_new_products() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $products = wc_get_products(array(
        'status'     => 'publish',
        'limit'      => 8,
        'orderby'    => 'date',
        'order'      => 'DESC',
        'visibility' => 'visible',
    ));

    if (empty($products)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-new-products py-5">
      <div class="container" style="max-width:1400px;">
        <div class="headshop-section-title">
          <span class="headshop-section-title__eyebrow">— Acabou de chegar</span>
          <div class="headshop-section-title__row">
            <h2 class="headshop-section-title__text">NOVIDADES</h2>
            <div class="headshop-section-title__rule"></div>
          </div>
        </div>
        <div class="headshop-products-carousel">
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--prev" aria-label="Anterior">&#8592;</button>
          <div class="headshop-new-products__grid headshop-products-carousel__track">
            <?php foreach ($products as $product) :
                $id         = $product->get_id();
                $name       = $product->get_name();
                $link       = get_permalink($id);
                $img_id     = $product->get_image_id();
                $img_url    = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : $placeholder;
                $price_html = $product->get_price_html();
            ?>
              <div class="headshop-new-products__card">
                <span class="headshop-new-products__badge">NOVO</span>
                <a href="<?= esc_url($link); ?>" class="headshop-new-products__image-link">
                  <div class="headshop-new-products__image-wrap">
                    <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($name); ?>" class="headshop-new-products__image" loading="lazy" />
                  </div>
                </a>
                <div class="headshop-new-products__info">
                  <h3 class="headshop-new-products__name"><?= esc_html($name); ?></h3>
                  <div class="headshop-new-products__price"><?= wp_kses_post($price_html); ?></div>
                  <a href="<?= esc_url($product->add_to_cart_url()); ?>"
                     class="headshop-carousel__add-btn"
                     data-product_id="<?= esc_attr($id); ?>"
                     data-quantity="1"
                     rel="nofollow">Adicionar</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--next" aria-label="Próximo">&#8594;</button>
        </div>
        <div class="text-center mt-4">
          <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn headshop-new-products__btn">Ver todos →</a>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   12. MOST VIEWED PRODUCTS — view counter + section
   ===================================================================== */

add_action('template_redirect', function () {
    if (!is_singular('product')) return;
    $id = get_the_ID();
    if (!$id) return;
    $count = (int) get_post_meta($id, 'post_views_count', true);
    update_post_meta($id, 'post_views_count', $count + 1);
});

function headshop_most_viewed_products() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $query = new WP_Query(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'meta_key'       => 'post_views_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'tax_query'      => array(array(
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => array('exclude-from-catalog'),
            'operator' => 'NOT IN',
        )),
    ));

    if (!$query->have_posts()) {
        $query = new WP_Query(array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 8,
            'orderby'        => 'rand',
        ));
    }

    if (!$query->have_posts()) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-viewed-products py-5">
      <div class="container" style="max-width:1400px;">
        <div class="headshop-section-title">
          <span class="headshop-section-title__eyebrow">— Os favoritos do momento</span>
          <div class="headshop-section-title__row">
            <h2 class="headshop-section-title__text">MAIS VISTOS</h2>
            <div class="headshop-section-title__rule"></div>
          </div>
        </div>
        <div class="headshop-products-carousel">
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--prev" aria-label="Anterior">&#8592;</button>
          <div class="headshop-viewed-products__grid headshop-products-carousel__track">
            <?php while ($query->have_posts()) : $query->the_post();
                $product    = wc_get_product(get_the_ID());
                if (!$product) continue;
                $id         = $product->get_id();
                $name       = $product->get_name();
                $link       = get_permalink($id);
                $img_id     = $product->get_image_id();
                $img_url    = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : $placeholder;
                $price_html = $product->get_price_html();
            ?>
              <div class="headshop-viewed-products__card">
                <a href="<?= esc_url($link); ?>" class="headshop-viewed-products__image-link">
                  <div class="headshop-viewed-products__image-wrap">
                    <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($name); ?>" class="headshop-viewed-products__image" loading="lazy" />
                  </div>
                </a>
                <div class="headshop-viewed-products__info">
                  <h3 class="headshop-viewed-products__name"><?= esc_html($name); ?></h3>
                  <div class="headshop-viewed-products__price"><?= wp_kses_post($price_html); ?></div>
                  <a href="<?= esc_url($product->add_to_cart_url()); ?>"
                     class="headshop-carousel__add-btn"
                     data-product_id="<?= esc_attr($id); ?>"
                     data-quantity="1"
                     rel="nofollow">Adicionar</a>
                </div>
              </div>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--next" aria-label="Próximo">&#8594;</button>
        </div>
        <div class="text-center mt-4">
          <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn headshop-viewed-products__btn">Ver todos →</a>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   13. MOST PURCHASED PRODUCTS — section
   ===================================================================== */

function headshop_most_purchased_products() {
    if (!is_front_page() || !class_exists('WooCommerce')) return;

    $products = wc_get_products(array(
        'status'     => 'publish',
        'limit'      => 8,
        'orderby'    => 'popularity',
        'order'      => 'DESC',
        'visibility' => 'visible',
    ));

    if (empty($products)) return;

    $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
    ?>
    <section class="headshop-purchased-products py-5">
      <div class="container" style="max-width:1400px;">
        <div class="headshop-section-title">
          <span class="headshop-section-title__eyebrow">— O que todo mundo está levando</span>
          <div class="headshop-section-title__row">
            <h2 class="headshop-section-title__text">+ PROCURADOS</h2>
            <div class="headshop-section-title__rule"></div>
          </div>
        </div>
        <div class="headshop-products-carousel">
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--prev" aria-label="Anterior">&#8592;</button>
          <div class="headshop-purchased-products__grid headshop-products-carousel__track">
            <?php foreach ($products as $product) :
                $id         = $product->get_id();
                $name       = $product->get_name();
                $link       = get_permalink($id);
                $img_id     = $product->get_image_id();
                $img_url    = $img_id ? wp_get_attachment_image_url($img_id, 'woocommerce_thumbnail') : $placeholder;
                $price_html = $product->get_price_html();
            ?>
              <div class="headshop-purchased-products__card">
                <a href="<?= esc_url($link); ?>" class="headshop-purchased-products__image-link">
                  <div class="headshop-purchased-products__image-wrap">
                    <img src="<?= esc_url($img_url); ?>" alt="<?= esc_attr($name); ?>" class="headshop-purchased-products__image" loading="lazy" />
                  </div>
                </a>
                <div class="headshop-purchased-products__info">
                  <h3 class="headshop-purchased-products__name"><?= esc_html($name); ?></h3>
                  <div class="headshop-purchased-products__price"><?= wp_kses_post($price_html); ?></div>
                  <a href="<?= esc_url($product->add_to_cart_url()); ?>"
                     class="headshop-carousel__add-btn"
                     data-product_id="<?= esc_attr($id); ?>"
                     data-quantity="1"
                     rel="nofollow">Adicionar</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button class="headshop-products-carousel__btn headshop-products-carousel__btn--next" aria-label="Próximo">&#8594;</button>
        </div>
        <div class="text-center mt-4">
          <a href="<?= esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn headshop-purchased-products__btn">Ver todos →</a>
        </div>
      </div>
    </section>
    <?php
}


/* =====================================================================
   14. IMPORTAR IMAGENS DE PRODUTOS (admin tool)
   ===================================================================== */

add_action('admin_menu', 'headshop_register_image_import_page');
function headshop_register_image_import_page() {
    add_submenu_page(
        'woocommerce',
        'Importar Imagens de Produtos',
        'Importar Imagens',
        'manage_woocommerce',
        'headshop-import-images',
        'headshop_image_import_page'
    );
}

function headshop_image_import_page() {
    if (!current_user_can('manage_woocommerce')) return;

    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $results = array();

    if (
        isset($_POST['headshop_import_nonce']) &&
        wp_verify_nonce($_POST['headshop_import_nonce'], 'headshop_import_images') &&
        !empty($_POST['headshop_image_list'])
    ) {
        $lines = explode("\n", sanitize_textarea_field(wp_unslash($_POST['headshop_image_list'])));

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;

            $parts = preg_split('/[\|,;\t]+/', $line, 2);
            if (count($parts) < 2) {
                $results[] = array('line' => $line, 'status' => 'error', 'msg' => 'Formato inválido — use: sku_ou_id|url');
                continue;
            }

            $identifier = trim($parts[0]);
            $img_url    = trim($parts[1]);

            if (empty($identifier) || empty($img_url)) {
                $results[] = array('line' => $line, 'status' => 'error', 'msg' => 'Identificador ou URL vazio');
                continue;
            }

            // Resolve product
            if (is_numeric($identifier)) {
                $post = get_post(intval($identifier));
                $product_id = ($post && $post->post_type === 'product') ? $post->ID : 0;
            } else {
                $product_id = wc_get_product_id_by_sku($identifier);
            }

            if (!$product_id) {
                $results[] = array('line' => $identifier, 'status' => 'error', 'msg' => 'Produto não encontrado');
                continue;
            }

            // Skip if already has image and overwrite not checked
            if (empty($_POST['headshop_overwrite']) && has_post_thumbnail($product_id)) {
                $results[] = array('line' => $identifier, 'status' => 'skip', 'msg' => 'Já possui imagem (ignorado)');
                continue;
            }

            // Download and attach image
            $attachment_id = media_sideload_image($img_url, $product_id, null, 'id');

            if (is_wp_error($attachment_id)) {
                $results[] = array('line' => $identifier, 'status' => 'error', 'msg' => $attachment_id->get_error_message());
                continue;
            }

            set_post_thumbnail($product_id, $attachment_id);
            $product_name = get_the_title($product_id);
            $results[] = array('line' => $identifier, 'status' => 'ok', 'msg' => 'OK — ' . esc_html($product_name));
        }
    }
    ?>
    <div class="wrap">
        <h1>Importar Imagens de Produtos</h1>
        <p>Cole abaixo uma linha por produto no formato <code>sku_ou_id|url_da_imagem</code>. Linhas começando com <code>#</code> são ignoradas.</p>

        <?php if (!empty($results)) : ?>
        <div style="background:#f9f9f9;border:1px solid #ddd;border-radius:6px;padding:16px;margin-bottom:20px;max-height:300px;overflow-y:auto;">
            <strong>Resultados:</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:10px;">
                <thead><tr style="background:#e8e8e8;">
                    <th style="text-align:left;padding:6px 10px;">Produto</th>
                    <th style="text-align:left;padding:6px 10px;">Status</th>
                    <th style="text-align:left;padding:6px 10px;">Mensagem</th>
                </tr></thead>
                <tbody>
                <?php foreach ($results as $r) :
                    $color = $r['status'] === 'ok' ? '#4caf50' : ($r['status'] === 'skip' ? '#ff9800' : '#f44336');
                ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:6px 10px;"><?= esc_html($r['line']); ?></td>
                    <td style="padding:6px 10px;color:<?= $color; ?>;font-weight:bold;"><?= esc_html(strtoupper($r['status'])); ?></td>
                    <td style="padding:6px 10px;"><?= esc_html($r['msg']); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('headshop_import_images', 'headshop_import_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="headshop_image_list">Lista de imagens</label></th>
                    <td>
                        <textarea id="headshop_image_list" name="headshop_image_list" rows="20" cols="80" class="large-text code"
                            placeholder="# Exemplo:&#10;meu-sku-001|https://site.com/imagem1.jpg&#10;123|https://site.com/imagem2.png"><?= isset($_POST['headshop_image_list']) ? esc_textarea(wp_unslash($_POST['headshop_image_list'])) : ''; ?></textarea>
                        <p class="description">Separadores aceitos: <code>|</code> (pipe), <code>,</code> (vírgula), <code>;</code> (ponto e vírgula) ou TAB.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="headshop_overwrite">Substituir imagem existente</label></th>
                    <td>
                        <input type="checkbox" id="headshop_overwrite" name="headshop_overwrite" value="1" />
                        <label for="headshop_overwrite">Sobrescrever produtos que já possuem imagem</label>
                    </td>
                </tr>
            </table>
            <?php submit_button('Importar Imagens', 'primary large'); ?>
        </form>
    </div>
    <?php
}


/* =====================================================================
   13. HIDE PAGE TITLE ON HOMEPAGE & ORDER-RECEIVED
   Order-received gets its own "Pedido confirmado" heading from the
   headshop-order-hero section (checkout/thankyou.php override) — the
   generic page title above it is redundant.
   ===================================================================== */

add_filter('the_title', function ($title, $post_id) {
    if (is_admin()) return $title;
    if ((is_front_page() || is_home()) && in_the_loop()) return '';
    if (function_exists('is_order_received_page') && is_order_received_page() && in_the_loop()) return '';
    return $title;
}, 10, 2);

/* =====================================================================
   WOOCOMMERCE — LOOP BUTTON TEXT
   ===================================================================== */

// "Ler mais" → "Adicionar" / "Adicionar ao carrinho" → "Adicionar"
add_filter('woocommerce_product_add_to_cart_text', function ($text, $product) {
    $map = array(
        __('Read more', 'woocommerce')        => 'Adicionar',
        __('Add to cart', 'woocommerce')      => 'Adicionar',
        __('Select options', 'woocommerce')   => 'Adicionar',
        __('View products', 'woocommerce')    => 'Adicionar',
    );
    return isset($map[$text]) ? $map[$text] : $text;
}, 10, 2);

// Strip default WC homepage sections
add_filter('the_content', function ($content) {
    if (!is_front_page()) return $content;
    $labels = array('Compre por categoria', 'Compre por marca', 'Favoritos dos fãs');
    $escaped = array_map(function ($s) { return preg_quote($s, '/'); }, $labels);
    $regex   = implode('|', $escaped);
    return preg_replace('/<h[1-6][^>]*>\s*(?:' . $regex . ')\s*<\/h[1-6]>.*?(?=(?:<h[1-6][^>]*>)|$)/is', '', $content);
}, 20);

/* =====================================================================
   14. DISTANCE-BASED SHIPPING
   Free within the configured radius above a minimum order value, flat
   rate within the radius below it, price-per-km beyond the radius.
   ===================================================================== */

require_once get_stylesheet_directory() . '/inc/class-headshop-shipping-distance.php';

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['headshop_distance_shipping'] = 'Headshop_Shipping_Distance';
    return $methods;
});

// One-time setup: add the method to the "Caruaru" zone and disable the
// old unconditional flat rate it replaces. Guarded by an option flag so
// it only runs once (admin can freely reconfigure/remove afterwards).
add_action('init', function () {
    if (get_option('headshop_distance_shipping_installed')) {
        return;
    }
    if (!class_exists('WC_Shipping_Zones')) {
        return;
    }

    foreach (WC_Shipping_Zones::get_zones() as $zone_data) {
        $zone = new WC_Shipping_Zone($zone_data['id']);

        $has_distance_method = false;
        foreach ($zone->get_shipping_methods() as $method) {
            if ('flat_rate' === $method->id) {
                $zone->delete_shipping_method($method->instance_id);
            }
            if ('headshop_distance_shipping' === $method->id) {
                $has_distance_method = true;
            }
        }

        if (!$has_distance_method) {
            $zone->add_shipping_method('headshop_distance_shipping');
        }
    }

    update_option('headshop_distance_shipping_installed', 1);
});

// Cart shipping calculator is CEP-only now (see main.css/custom.js) — the
// stock "Enter your address to view shipping options." prompt no longer
// applies; an info icon next to the field explains it instead.
add_filter('woocommerce_shipping_may_be_available_html', '__return_empty_string');

// One-time setup: add "Retirada no local" (WooCommerce's built-in
// local_pickup method) to every zone, free of charge, so customers can
// pick up in-store as an alternative to delivery.
//
// Note: WooCommerce also has a newer "Pickup Location" feature
// (pickup_location), but that one only registers when the Checkout
// *block* is in use (see ShippingController::register_local_pickup in
// the WooCommerce plugin) — this store's checkout is the classic
// [woocommerce_checkout] shortcode, so local_pickup is the method that
// actually works here.
add_action('init', function () {
    if (get_option('headshop_local_pickup_installed')) {
        return;
    }
    if (!class_exists('WC_Shipping_Zones')) {
        return;
    }

    foreach (WC_Shipping_Zones::get_zones() as $zone_data) {
        $zone = new WC_Shipping_Zone($zone_data['id']);

        $has_local_pickup = false;
        foreach ($zone->get_shipping_methods() as $method) {
            if ('local_pickup' === $method->id) {
                $has_local_pickup = true;
            }
        }

        if (!$has_local_pickup) {
            $instance_id = $zone->add_shipping_method('local_pickup');
            if ($instance_id) {
                update_option('woocommerce_local_pickup_' . $instance_id . '_settings', [
                    'title'      => 'Retirada no local',
                    'tax_status' => 'taxable',
                    'cost'       => '',
                ]);
            }
        }
    }

    update_option('headshop_local_pickup_installed', 1);
});

/* =====================================================================
   15. ASAAS CREDIT CARD — guard against corrupted "split_wallet" setting
   Two stacked bugs in the woo-asaas plugin's split-payment feature:
   1. Its sanitizer (Split_Settings_Service::sanitize_split_wallet_field)
      can save this setting as an empty string instead of an array when
      no split wallet rows are configured, and its consumer
      (Split_Checkout_Hook::split_payment_data) only guards against
      null — a string value fatals on array_map() during checkout.
   2. Even a valid *empty* array reaches Split_Gateway_Log_Service::log(),
      which throws "Settings cannot be empty." instead of treating "no
      split configured" as a no-op.
   Both were breaking every credit card order ("Houve um erro ao
   processar sua compra..."). split_payment_data() already has a working
   early-return for null, so route anything that isn't a *non-empty*
   array through that instead of introducing a new code path — fixed
   here (not in the plugin) since wp-content/plugins is gitignored and
   overwritten on updates.
   ===================================================================== */
add_filter('woocommerce_asaas_payment_data', function ($payment_data, $wc_order, $gateway) {
    if (isset($gateway->settings['split_wallet']) && empty($gateway->settings['split_wallet'])) {
        $gateway->settings['split_wallet'] = null;
    }
    return $payment_data;
}, 5, 3);


/* =====================================================================
   16. CHECKOUT — REQUIRE LOGIN
   Reduz risco de fraude: visitante anônimo que tentar acessar o
   checkout é redirecionado para Minha Conta (login/cadastro) antes de
   ver o formulário. Volta automática ao checkout é feita pelo campo
   oculto "redirect" (ver função headshop_checkout_redirect_field
   abaixo), lido nativamente por WC_Form_Handler::process_login() e
   process_registration(). order-pay/order-received ficam de fora pois
   já assumem que o pedido existe — não devem exigir novo login.
   ===================================================================== */

add_action('template_redirect', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_user_logged_in()) {
        return;
    }
    if (is_wc_endpoint_url('order-pay') || is_wc_endpoint_url('order-received')) {
        return;
    }

    wc_add_notice(
        __('Faça login ou crie sua conta para finalizar a compra com segurança.', 'headshop'),
        'notice'
    );

    $redirect = add_query_arg(
        'redirect_to',
        rawurlencode(wc_get_checkout_url()),
        wc_get_page_permalink('myaccount')
    );

    wp_safe_redirect($redirect);
    exit;
});

// WooCommerce já injeta esse campo nativamente no formulário de login
// (Automattic\WooCommerce\Blocks\BlockTypesController::redirect_to_field,
// hook woocommerce_login_form_end) — só falta no de cadastro, cobrimos
// apenas esse aqui para não duplicar o que o core já faz.
add_action('woocommerce_register_form_start', function () {
    if (empty($_GET['redirect_to'])) {
        return;
    }
    $target = wp_validate_redirect(
        esc_url_raw(wp_unslash($_GET['redirect_to'])),
        ''
    );
    if (!$target) {
        return;
    }
    printf('<input type="hidden" name="redirect" value="%s" />', esc_attr($target));
});
