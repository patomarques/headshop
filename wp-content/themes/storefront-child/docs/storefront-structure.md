# Storefront Theme Structure

## Required HTML skeleton

Storefront's CSS assumes this exact nesting:

```
#page
  #masthead
  #content
    .col-full      ← content must live here
```

Breaking this structure causes layout to fall outside the container and lose parent styles.

## header.php / footer.php conditional logic

The banner on the homepage must be **full-width** (outside `.col-full`). To achieve this without duplicating the wrapper logic, `header.php` conditionally opens `.col-full` only on non-front pages:

```php
if (!is_front_page()) {
    echo '<div class="col-full">';
}
```

`footer.php` mirrors this — it closes `.col-full` only on non-front pages:

```php
if (!is_front_page()) {
    echo '</div>'; // .col-full
}
```

Each page template (e.g. `homepage.php`) is responsible for its own `.col-full` wrappers around its sections.

## Style enqueue dependency

The child theme's `main.css` must declare `storefront-style` as a dependency — not `storefront-parent-style`:

```php
wp_enqueue_style('storefront-child-main', ..., ['storefront-style', 'bootstrap-cdn'], '1.0.0');
```

Storefront's `child_scripts()` method auto-enqueues `style.css` under the handle `storefront-style`. Do not re-enqueue the parent stylesheet manually.

## WP_Query vs wc_get_products() in WP-CLI eval-file

`wc_get_products()` returns 0 results when run via `wp eval-file` because WooCommerce is not fully initialized in that context. Use `WP_Query` with `post_type = product` and `meta_query` directly instead.
