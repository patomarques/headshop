# Checkout Identification Gate

## Overview

Replace the checkout login panel with a full identification gate. Non-logged-in users see an identification page instead of the checkout form. Login or registration is mandatory — no guest checkout.

## Architecture

### Entry point: `woocommerce/checkout/form-checkout.php`

Override the WooCommerce checkout template. At the top of the file, check `is_user_logged_in()`:

- **Not logged in** → render the identification page HTML and `return`. The checkout form never reaches the DOM.
- **Logged in** → render the normal WooCommerce checkout form.

The existing `woocommerce/checkout/form-login.php` (tab panel) becomes obsolete and can be deleted.

### Guest checkout disabled

Add a filter in `functions.php`:

```php
add_filter('woocommerce_checkout_registration_required', '__return_true');
add_filter('pre_option_woocommerce_enable_guest_checkout', fn() => 'no');
```

This ensures WooCommerce never processes an order from a non-authenticated user, even if the gate is bypassed.

### CSS safety layer

In `_checkout.scss`, add:

```scss
body:not(.logged-in) .checkout.woocommerce-checkout {
  display: none !important;
}
```

Belt-and-suspenders: even if PHP gate is somehow skipped, the checkout form is invisible to guests.

## Identification Page Layout

```
┌─────────────────────────────────────────────────┐
│              Identificação                       │
├────────────────────┬────────────────────────────┤
│  Quero criar uma   │  Já sou cliente             │
│  conta             │                             │
│                    │  [E-mail ou usuário    ]    │
│  [E-mail      ]    │  [Senha          👁   ]    │
│                    │                             │
│  [  Continuar  ]   │  [     Continuar       ]    │
│                    │  Esqueci minha senha        │
├────────────────────┴────────────────────────────┤
│     ─────── Use sua conta Google ───────        │
│            [ G  Fazer login ]                   │
└─────────────────────────────────────────────────┘
```

### Left panel — "Quero criar uma conta"

- Single email `<input>` field
- "Continuar" button submits a `<form method="GET">` that redirects to `{myaccount_url}?action=register&email={value}`
- No server-side processing needed

### Right panel — "Já sou cliente"

- Uses `woocommerce_login_form(['redirect' => wc_get_checkout_url()])` — reuses existing WC authentication logic, nonces, and error handling
- Password field includes show/hide toggle (same pattern as `myaccount/form-login.php`)
- "Esqueci minha senha" link → `wp_lostpassword_url()`

### Google OAuth footer

- Conditionally rendered: only if `get_option('eletronicos_google_client_id')` is set
- `eletronicos_google_auth_url()` receives an optional `$redirect` parameter (e.g. `wc_get_checkout_url()`). The redirect URL is stored in a WordPress transient keyed by the nonce (`eletronicos_google_redirect_{nonce}`, TTL 5 min)
- The callback handler in `inc-google-auth.php` (line 131) is updated to read the transient and redirect there instead of always going to the dashboard
- Existing myaccount usage passes no argument and continues to redirect to the dashboard

## Styles

All styles go in `_checkout.scss` (already imported in `main.scss`).

New classes: `.checkout-id-*` namespace.

- `.checkout-id-wrap` — page wrapper, max-width centered
- `.checkout-id-title` — "Identificação" heading
- `.checkout-id-panels` — CSS Grid, two equal columns, `gap`, vertical divider via `::after` pseudo-element
- `.checkout-id-panel` — individual panel
- `.checkout-id-panel__heading` — panel title
- `.checkout-id-google` — Google button footer, full width, centered
- Mobile (`respond-to(md)`): panels stack vertically, login panel first

The existing `.checkout-auth-*` block in `_checkout.scss` can be removed since the tab panel is being replaced.

## Files Changed

| File | Action |
|------|--------|
| `woocommerce/checkout/form-checkout.php` | Create (copy from WC core + gate logic at top) |
| `woocommerce/checkout/form-login.php` | Delete (replaced by gate in form-checkout.php) |
| `functions.php` | Add 2 filters to disable guest checkout |
| `assets/scss/pages/_checkout.scss` | Replace `.checkout-auth-*` with `.checkout-id-*` + CSS safety layer |
| `inc-google-auth.php` | Update `eletronicos_google_auth_url()` to accept `$redirect` param + update callback to use transient redirect |

## Out of Scope

- CPF/CNPJ login (WooCommerce natively handles email/username only)
- Magic link / passwordless login
- Inline registration form (redirects to existing myaccount register page)
