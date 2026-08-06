# Checkout Identification Gate Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the checkout login panel with a mandatory identification gate that shows "Quero criar uma conta" and "Já sou cliente" side-by-side, with Google OAuth, blocking all guest checkout.

**Architecture:** Override `woocommerce/checkout/form-checkout.php` to render the identification page for non-logged-in users and `return` before the checkout form. Two PHP filters enforce no-guest-checkout at the WooCommerce level. A CSS safety layer hides `.checkout.woocommerce-checkout` for non-authenticated visitors.

**Tech Stack:** PHP 8+, WordPress hooks/filters, WooCommerce template override, SCSS (compiled with `npm run build` from theme root)

---

### Task 1: Add redirect support to Google OAuth

**Files:**
- Modify: `wp-content/themes/storefront-child/inc-google-auth.php`

The current `eletronicos_google_auth_url()` always redirects to the account dashboard after login (line 131). We need it to redirect to checkout when called from the checkout gate. Solution: accept an optional `$redirect` param, store it in a transient keyed by nonce, and read it back in the callback.

- [ ] **Step 1: Update `eletronicos_google_auth_url()` to accept `$redirect`**

Replace lines 4–17 of `inc-google-auth.php`:

```php
function eletronicos_google_auth_url($redirect = '') {
    $client_id    = get_option('eletronicos_google_client_id', '');
    $redirect_uri = home_url('/google-auth-callback/');
    $state        = wp_create_nonce('eletronicos_google_state');

    if ($redirect) {
        set_transient('eletronicos_google_redirect_' . $state, esc_url_raw($redirect), 5 * MINUTE_IN_SECONDS);
    }

    return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
        'client_id'     => $client_id,
        'redirect_uri'  => $redirect_uri,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'prompt'        => 'select_account',
    ]);
}
```

- [ ] **Step 2: Update callback to read transient and redirect correctly**

Replace line 131 (the final `wp_redirect` before `exit`) in the `template_redirect` action:

```php
    $redirect_after = get_transient('eletronicos_google_redirect_' . $state);
    delete_transient('eletronicos_google_redirect_' . $state);

    wp_redirect($redirect_after ?: wc_get_account_endpoint_url('dashboard'));
    exit;
```

The `$state` variable is already in scope at that point (verified at line 44). Existing myaccount usage passes no argument, so `$redirect_after` is `false` and falls back to the dashboard — no behaviour change there.

- [ ] **Step 3: Verify syntax is valid**

Run from the theme root:
```bash
php -l wp-content/themes/storefront-child/inc-google-auth.php
```
Expected output: `No syntax errors detected in inc-google-auth.php`

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/storefront-child/inc-google-auth.php
git commit -m "feat(google-auth): support redirect param after OAuth login"
```

---

### Task 2: Disable guest checkout

**Files:**
- Modify: `wp-content/themes/storefront-child/functions.php`

- [ ] **Step 1: Add filters at the end of `functions.php`**

Append after the last closing `};` (after line 66):

```php
add_filter('woocommerce_checkout_registration_required', '__return_true');
add_filter('pre_option_woocommerce_enable_guest_checkout', fn() => 'no');
```

- [ ] **Step 2: Verify syntax**

```bash
php -l wp-content/themes/storefront-child/functions.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add wp-content/themes/storefront-child/functions.php
git commit -m "feat(checkout): disable guest checkout, require login"
```

---

### Task 3: Rebuild checkout SCSS

**Files:**
- Modify: `wp-content/themes/storefront-child/assets/scss/pages/_checkout.scss`

Replace the entire `.checkout-auth-*` block (lines 1–146) with `.checkout-id-*` classes and add the CSS safety layer. Keep the `.woocommerce-checkout` block (lines 148–311) intact.

- [ ] **Step 1: Replace the top section of `_checkout.scss`**

The file currently starts with the comment `// ── Painel de login/registro no checkout` on line 5 and the `.checkout-auth-panel` block runs to line 146. Replace everything from line 1 up to (but not including) line 148 with:

```scss
@use 'sass:color';
@use '../abstracts/variables' as *;
@use '../abstracts/mixins' as *;

// ── CSS safety layer: hide checkout form for guests ───────────────────────────
body:not(.logged-in) .checkout.woocommerce-checkout {
  display: none !important;
}

// ── Identification gate ───────────────────────────────────────────────────────
.checkout-id-wrap {
  max-width: 860px;
  margin: 0 auto;
  padding: $spacing-xl $spacing-md;
}

.checkout-id-title {
  font-size: $font-size-lg;
  font-weight: $font-weight-bold;
  color: $color-primary;
  margin-bottom: $spacing-xl;
}

.checkout-id-panels {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border: 1px solid $color-border;
  border-radius: $radius-md;
  overflow: hidden;
  background: $color-white;
}

.checkout-id-panel {
  padding: $spacing-xl $spacing-lg;

  &:first-child {
    border-right: 1px solid $color-border;
  }
}

.checkout-id-panel__heading {
  font-size: $font-size-base;
  font-weight: $font-weight-bold;
  color: $color-dark;
  margin-bottom: $spacing-lg;
}

// Left panel – new account form
.checkout-id-new-form {
  .checkout-id-field {
    margin-bottom: $spacing-md;

    input {
      width: 100%;
      padding: 0.6rem $spacing-md;
      border: 1.5px solid $color-border;
      border-radius: $radius-md;
      font-size: $font-size-base;
      color: $color-dark;
      background: $color-white;
      transition: border-color 0.2s ease;

      &:focus {
        outline: none;
        border-color: $color-primary;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
      }
    }
  }

  .checkout-id-submit {
    width: 100%;
    padding: 0.65rem;
    background: $color-primary;
    color: $color-white;
    border: none;
    border-radius: $radius-md;
    font-size: $font-size-base;
    font-weight: $font-weight-bold;
    cursor: pointer;
    transition: background 0.2s ease;

    &:hover { background: color.adjust($color-primary, $lightness: -8%); }
  }
}

// Right panel – WC login form overrides
.checkout-id-panel .woocommerce-form-login {
  .form-row { margin-bottom: $spacing-md; }

  label {
    display: block;
    font-size: $font-size-sm;
    font-weight: 600;
    color: $color-dark;
    margin-bottom: $spacing-xs;
  }

  .woocommerce-Input {
    width: 100%;
    padding: 0.6rem $spacing-md;
    border: 1.5px solid $color-border;
    border-radius: $radius-md;
    font-size: $font-size-base;
    color: $color-dark;
    background: $color-white;
    transition: border-color 0.2s ease;

    &:focus {
      outline: none;
      border-color: $color-primary;
      box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }
  }

  // hide "remember me" checkbox — not needed in checkout context
  .woocommerce-form__label-for-checkbox,
  .woocommerce-form-login__rememberme { display: none; }

  .woocommerce-button {
    width: 100%;
    padding: 0.65rem;
    background: $color-primary;
    color: $color-white;
    border: none;
    border-radius: $radius-md;
    font-size: $font-size-base;
    font-weight: $font-weight-bold;
    cursor: pointer;
    transition: background 0.2s ease;
    margin-top: $spacing-sm;

    &:hover { background: color.adjust($color-primary, $lightness: -8%); }
  }
}

// Password show/hide toggle (injected by inline JS)
.checkout-id-pw-wrap {
  position: relative;

  input { padding-right: 2.5rem; }

  .checkout-id-pw-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: $color-muted;
    padding: 0;
    line-height: 1;

    &:hover { color: $color-dark; }
  }
}

.checkout-id-lostpass {
  margin-top: $spacing-sm;
  font-size: $font-size-sm;
  text-align: right;

  a { color: $color-muted; &:hover { color: $color-primary; } }
}

// Google section
.checkout-id-google {
  border-top: 1px solid $color-border;
  padding: $spacing-lg;
  text-align: center;
  background: $color-white;
}

.checkout-id-google__label {
  font-size: $font-size-sm;
  color: $color-secondary;
  margin-bottom: $spacing-md;
}

.checkout-id-google__btn {
  display: inline-flex;
  align-items: center;
  gap: $spacing-sm;
  padding: 0.6rem $spacing-lg;
  border: 1.5px solid $color-border;
  border-radius: $radius-md;
  background: $color-white;
  color: $color-dark;
  font-size: $font-size-sm;
  font-weight: 600;
  text-decoration: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;

  svg { flex: 0 0 auto; }

  &:hover {
    border-color: #4285F4;
    box-shadow: 0 2px 8px rgba(66, 133, 244, 0.2);
    color: $color-dark;
    text-decoration: none;
  }
}

// Mobile
@include respond-to(md) {
  .checkout-id-panels {
    grid-template-columns: 1fr;
  }

  .checkout-id-panel:first-child {
    border-right: none;
    border-bottom: 1px solid $color-border;
  }

  .checkout-id-wrap {
    padding: $spacing-lg $spacing-md;
  }
}
```

- [ ] **Step 2: Compile SCSS**

Run from the theme root (`/var/www/html/eletronicos/wp-content/themes/storefront-child`):
```bash
cd /var/www/html/eletronicos/wp-content/themes/storefront-child && npm run build
```
Expected: no errors, `assets/css/main.css` updated.

- [ ] **Step 3: Commit**

```bash
git add wp-content/themes/storefront-child/assets/scss/pages/_checkout.scss \
        wp-content/themes/storefront-child/assets/css/main.css
git commit -m "feat(checkout): identification gate styles, remove auth-panel block"
```

---

### Task 4: Create `form-checkout.php` and remove `form-login.php`

**Files:**
- Create: `wp-content/themes/storefront-child/woocommerce/checkout/form-checkout.php`
- Delete: `wp-content/themes/storefront-child/woocommerce/checkout/form-login.php`

- [ ] **Step 1: Create `woocommerce/checkout/form-checkout.php`**

```php
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
          <p class="checkout-id-lostpass">
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Esqueci minha senha', 'storefront-child'); ?></a>
          </p>
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
      btn.innerHTML = '<svg class="eye-show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg><svg class="eye-hide" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" style="display:none"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>';
      wrap.appendChild(btn);

      btn.addEventListener('click', function () {
        var show = btn.querySelector('.eye-show');
        var hide = btn.querySelector('.eye-hide');
        if (pwInput.type === 'password') {
          pwInput.type = 'text';
          show.style.display = 'none';
          hide.style.display = '';
          btn.setAttribute('aria-label', 'Ocultar senha');
        } else {
          pwInput.type = 'password';
          show.style.display = '';
          hide.style.display = 'none';
          btn.setAttribute('aria-label', 'Mostrar senha');
        }
      });
    })();
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
```

- [ ] **Step 2: Verify PHP syntax**

```bash
php -l wp-content/themes/storefront-child/woocommerce/checkout/form-checkout.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Delete obsolete `form-login.php`**

```bash
git rm wp-content/themes/storefront-child/woocommerce/checkout/form-login.php
```

- [ ] **Step 4: Commit**

```bash
git add wp-content/themes/storefront-child/woocommerce/checkout/form-checkout.php
git commit -m "feat(checkout): identification gate — login/register required before checkout"
```

---

### Task 5: Final verification

- [ ] **Step 1: Open checkout as a logged-out user**

Visit `/checkout/` in a private/incognito browser window. Expected:
- Page shows "Identificação" heading
- Two panels: "Quero criar uma conta" (left) | "Já sou cliente" (right)
- Google button visible if `eletronicos_google_client_id` is set in WP options
- No checkout fields (billing, shipping, payment) in the DOM

- [ ] **Step 2: Verify CSS safety layer**

In browser DevTools, confirm `.checkout.woocommerce-checkout` is not present in the DOM (because `return` prevents it from being rendered) — the CSS rule is belt-and-suspenders.

- [ ] **Step 3: Test login flow**

Fill in the "Já sou cliente" form with valid credentials and submit. Expected:
- WooCommerce authenticates the user
- Page reloads and shows the full checkout form (billing, shipping, payment, place order button)

- [ ] **Step 4: Test "Quero criar uma conta"**

Fill in an email and click "Continuar". Expected:
- Redirects to `/minha-conta/?action=register&email=test%40example.com`

- [ ] **Step 5: Final SCSS compile**

```bash
cd /var/www/html/eletronicos/wp-content/themes/storefront-child && npm run build
```
Expected: no errors.

- [ ] **Step 6: Commit compiled CSS if any change**

```bash
git add wp-content/themes/storefront-child/assets/css/main.css
git diff --cached --quiet || git commit -m "build: recompile main.css"
```
