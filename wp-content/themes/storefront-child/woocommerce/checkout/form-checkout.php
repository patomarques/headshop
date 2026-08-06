<?php
/**
 * Checkout Form with identification gate.
 * @see woocommerce/templates/checkout/form-checkout.php (WC 9.4.0)
 */
defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    $google_client_id = get_option('eletronicos_google_client_id', '');
    $register_url     = add_query_arg('action', 'register', wc_get_page_permalink('myaccount'));
    ?>
    <div class="checkout-id-wrap">

      <h2 class="checkout-id-title"><?php esc_html_e('Identificação', 'storefront-child'); ?></h2>

      <div class="checkout-id-panels">

        <div class="checkout-id-panel">
          <h3 class="checkout-id-panel__heading"><?php esc_html_e('Quero criar uma conta', 'storefront-child'); ?></h3>
          <form class="checkout-id-new-form" method="GET" action="<?php echo esc_url($register_url); ?>">
            <div class="checkout-id-field">
              <input
                type="email"
                name="email"
                placeholder="<?php esc_attr_e('Digite seu e-mail', 'storefront-child'); ?>"
                autocomplete="email"
                required
              >
            </div>
            <button type="submit" class="checkout-id-submit">
              <?php esc_html_e('Continuar', 'storefront-child'); ?>
            </button>
          </form>
        </div>

        <div class="checkout-id-panel">
          <h3 class="checkout-id-panel__heading"><?php esc_html_e('Já sou cliente', 'storefront-child'); ?></h3>
          <?php
          woocommerce_login_form([
            'message'  => '',
            'redirect' => wc_get_checkout_url(),
            'hidden'   => false,
          ]);
          ?>
        </div>

      </div>

      <?php if ($google_client_id): ?>
      <div class="checkout-id-google">
        <p class="checkout-id-google__label"><?php esc_html_e('Use sua conta Google', 'storefront-child'); ?></p>
        <a href="<?php echo esc_url(eletronicos_google_auth_url(wc_get_checkout_url())); ?>"
           class="checkout-id-google__btn"
           aria-label="<?php esc_attr_e('Fazer login com Google', 'storefront-child'); ?>">
          <svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          <span><?php esc_html_e('Fazer login com Google', 'storefront-child'); ?></span>
        </a>
      </div>
      <?php endif; ?>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
      (function () {
        var pwInput = document.querySelector('.checkout-id-panel .woocommerce-form-login #password');
        if (!pwInput) return;

        var wrap = document.createElement('div');
        wrap.className = 'checkout-id-pw-wrap';
        pwInput.parentNode.insertBefore(wrap, pwInput);
        wrap.appendChild(pwInput);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'checkout-id-pw-toggle';
        btn.setAttribute('aria-label', 'Mostrar senha');
        btn.innerHTML = '<svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg><svg class="eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>';
        wrap.appendChild(btn);

        btn.addEventListener('click', function () {
          var visible = pwInput.type !== 'password';
          pwInput.type = visible ? 'password' : 'text';
          btn.classList.toggle('pw-visible', !visible);
          btn.setAttribute('aria-label', visible ? 'Mostrar senha' : 'Ocultar senha');
        });
      })();
    });
    </script>
    <?php
    return;
}

// Logged-in: render normal checkout form
do_action('woocommerce_before_checkout_form', $checkout);
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout"
      action="<?php echo esc_url(wc_get_checkout_url()); ?>"
      enctype="multipart/form-data"
      aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">

  <?php if ($checkout->get_checkout_fields()): ?>
    <?php do_action('woocommerce_checkout_before_customer_details'); ?>
    <div class="col2-set" id="customer_details">
      <div class="col-1"><?php do_action('woocommerce_checkout_billing'); ?></div>
      <div class="col-2"><?php do_action('woocommerce_checkout_shipping'); ?></div>
    </div>
    <?php do_action('woocommerce_checkout_after_customer_details'); ?>
  <?php endif; ?>

  <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
  <h3 id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h3>
  <?php do_action('woocommerce_checkout_before_order_review'); ?>

  <div id="order_review" class="woocommerce-checkout-review-order">
    <?php do_action('woocommerce_checkout_order_review'); ?>
  </div>

  <?php do_action('woocommerce_checkout_after_order_review'); ?>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
