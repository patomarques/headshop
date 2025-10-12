# ⚙️ Configuração Avançada do Tema Storefront Child

## 📋 Configurações Recomendadas

### 1. Configurações do WordPress

#### wp-config.php
```php
// Configurações recomendadas
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Configurações de memória
ini_set('memory_limit', '256M');

// Configurações de upload
define('WP_MEMORY_LIMIT', '256M');
define('MAX_EXECUTION_TIME', 300);

// Configurações de cache
define('WP_CACHE', true);
```

### 2. Configurações do WooCommerce

#### Produtos por Página
- **Loja**: 12 produtos
- **Categorias**: 12 produtos
- **Tags**: 12 produtos
- **Busca**: 12 produtos

#### Configurações de Imagem
- **Miniatura do catálogo**: 300x300px
- **Imagem única do produto**: 600x600px
- **Miniatura da galeria**: 100x100px

#### Configurações de Checkout
- **País padrão**: Brasil
- **Moeda**: Real brasileiro (R$)
- **Formato de data**: dd/mm/aaaa

### 3. Configurações do Tema

#### Cores Padrão
```css
:root {
    --primary-color: #e74c3c;
    --secondary-color: #2c3e50;
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --info-color: #3498db;
    --light-color: #ecf0f1;
    --dark-color: #2c3e50;
}
```

#### Tipografia
```css
body {
    font-family: 'Open Sans', sans-serif;
    font-size: 16px;
    line-height: 1.6;
    color: #333;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Montserrat', sans-serif;
    font-weight: 600;
    line-height: 1.2;
}
```

## 🔧 Personalizações Avançadas

### 1. Adicionar Fontes Customizadas

```php
// Adicionar ao functions.php
function storefront_child_custom_fonts() {
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap');
}
add_action('wp_enqueue_scripts', 'storefront_child_custom_fonts');
```

### 2. Adicionar CSS Customizado

```php
// Adicionar ao functions.php
function storefront_child_custom_css() {
    $custom_css = get_theme_mod('storefront_child_custom_css', '');
    if ($custom_css) {
        echo '<style type="text/css">' . $custom_css . '</style>';
    }
}
add_action('wp_head', 'storefront_child_custom_css');
```

### 3. Adicionar JavaScript Customizado

```php
// Adicionar ao functions.php
function storefront_child_custom_js() {
    $custom_js = get_theme_mod('storefront_child_custom_js', '');
    if ($custom_js) {
        echo '<script type="text/javascript">' . $custom_js . '</script>';
    }
}
add_action('wp_footer', 'storefront_child_custom_js');
```

## 🎨 Customizações Visuais

### 1. Logo Personalizado

```php
// Adicionar suporte a logo
function storefront_child_custom_logo_setup() {
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
}
add_action('after_setup_theme', 'storefront_child_custom_logo_setup');
```

### 2. Favicon Personalizado

```php
// Adicionar favicon
function storefront_child_favicon() {
    echo '<link rel="icon" type="image/x-icon" href="' . get_stylesheet_directory_uri() . '/assets/images/favicon.ico">';
}
add_action('wp_head', 'storefront_child_favicon');
```

### 3. Cores do Admin

```php
// Personalizar cores do admin
function storefront_child_admin_colors() {
    echo '<style>
        #wpadminbar { background: #2c3e50 !important; }
        #wpadminbar .ab-top-menu > li > a { color: #ffffff !important; }
        #wpadminbar .ab-top-menu > li:hover > a { background: #e74c3c !important; }
    </style>';
}
add_action('admin_head', 'storefront_child_admin_colors');
```

## 🛒 Personalizações do WooCommerce

### 1. Campos Customizados

```php
// Adicionar campos ao checkout
function storefront_child_checkout_fields($fields) {
    $fields['billing']['billing_cpf'] = array(
        'label' => 'CPF',
        'placeholder' => '000.000.000-00',
        'required' => true,
        'class' => array('form-row-wide'),
    );
    
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'storefront_child_checkout_fields');
```

### 2. Validação Customizada

```php
// Validar CPF
function storefront_child_validate_cpf($data, $errors) {
    if (empty($data['billing_cpf'])) {
        $errors->add('billing_cpf', 'CPF é obrigatório.');
    }
}
add_action('woocommerce_checkout_process', 'storefront_child_validate_cpf', 10, 2);
```

### 3. Emails Personalizados

```php
// Personalizar emails
function storefront_child_email_styles($css) {
    $css .= '
        .woocommerce-email-header {
            background: #2c3e50;
            color: #ffffff;
            padding: 20px;
        }
        .woocommerce-email-footer {
            background: #e74c3c;
            color: #ffffff;
            padding: 20px;
        }
    ';
    return $css;
}
add_filter('woocommerce_email_styles', 'storefront_child_email_styles');
```

## 📱 Configurações Mobile

### 1. Viewport Meta Tag

```php
// Adicionar viewport
function storefront_child_viewport() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
}
add_action('wp_head', 'storefront_child_viewport');
```

### 2. Touch Icons

```php
// Adicionar touch icons
function storefront_child_touch_icons() {
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . get_stylesheet_directory_uri() . '/assets/images/apple-touch-icon.png">';
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . get_stylesheet_directory_uri() . '/assets/images/favicon-32x32.png">';
    echo '<link rel="icon" type="image/png" sizes="16x16" href="' . get_stylesheet_directory_uri() . '/assets/images/favicon-16x16.png">';
}
add_action('wp_head', 'storefront_child_touch_icons');
```

## 🔍 Configurações de SEO

### 1. Meta Tags

```php
// Adicionar meta tags
function storefront_child_meta_tags() {
    if (is_home() || is_front_page()) {
        echo '<meta name="description" content="' . get_bloginfo('description') . '">';
        echo '<meta name="keywords" content="loja online, e-commerce, produtos">';
    }
}
add_action('wp_head', 'storefront_child_meta_tags');
```

### 2. Open Graph

```php
// Adicionar Open Graph
function storefront_child_open_graph() {
    if (is_single() || is_page()) {
        global $post;
        $image = get_the_post_thumbnail_url($post->ID, 'large');
        
        echo '<meta property="og:title" content="' . get_the_title() . '">';
        echo '<meta property="og:description" content="' . get_the_excerpt() . '">';
        echo '<meta property="og:image" content="' . $image . '">';
        echo '<meta property="og:url" content="' . get_permalink() . '">';
        echo '<meta property="og:type" content="website">';
    }
}
add_action('wp_head', 'storefront_child_open_graph');
```

## ⚡ Configurações de Performance

### 1. Minificação

```php
// Minificar CSS e JS
function storefront_child_minify_assets() {
    if (!is_admin()) {
        // Minificar CSS
        ob_start('storefront_child_minify_css');
        
        // Minificar JS
        ob_start('storefront_child_minify_js');
    }
}
add_action('init', 'storefront_child_minify_assets');

function storefront_child_minify_css($buffer) {
    $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
    $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
    return $buffer;
}

function storefront_child_minify_js($buffer) {
    $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
    $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
    return $buffer;
}
```

### 2. Cache

```php
// Configurar cache
function storefront_child_cache_headers() {
    if (!is_admin()) {
        header('Cache-Control: public, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    }
}
add_action('send_headers', 'storefront_child_cache_headers');
```

## 🛡️ Configurações de Segurança

### 1. Headers de Segurança

```php
// Adicionar headers de segurança
function storefront_child_security_headers() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
add_action('send_headers', 'storefront_child_security_headers');
```

### 2. Remover Informações do WordPress

```php
// Remover informações do WordPress
function storefront_child_remove_wp_info() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
}
add_action('init', 'storefront_child_remove_wp_info');
```

## 📊 Configurações de Analytics

### 1. Google Analytics

```php
// Adicionar Google Analytics
function storefront_child_google_analytics() {
    $ga_id = get_theme_mod('storefront_child_ga_id', '');
    if ($ga_id) {
        echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $ga_id . '"></script>';
        echo '<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag("js", new Date());gtag("config", "' . $ga_id . '");</script>';
    }
}
add_action('wp_head', 'storefront_child_google_analytics');
```

### 2. Facebook Pixel

```php
// Adicionar Facebook Pixel
function storefront_child_facebook_pixel() {
    $pixel_id = get_theme_mod('storefront_child_pixel_id', '');
    if ($pixel_id) {
        echo '<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,"script","https://connect.facebook.net/en_US/fbevents.js");fbq("init","' . $pixel_id . '");fbq("track","PageView");</script>';
    }
}
add_action('wp_head', 'storefront_child_facebook_pixel');
```

## 🔧 Configurações de Desenvolvimento

### 1. Modo Debug

```php
// Ativar modo debug
function storefront_child_debug_mode() {
    if (WP_DEBUG) {
        echo '<div style="position: fixed; bottom: 0; left: 0; background: #000; color: #fff; padding: 10px; z-index: 9999; font-size: 12px;">';
        echo 'Debug Mode: ON | Memory: ' . size_format(memory_get_usage()) . ' | Time: ' . timer_stop() . 's';
        echo '</div>';
    }
}
add_action('wp_footer', 'storefront_child_debug_mode');
```

### 2. Log de Erros

```php
// Log de erros customizado
function storefront_child_error_log($message) {
    if (WP_DEBUG_LOG) {
        error_log('Storefront Child: ' . $message);
    }
}
```

---

**📝 Nota**: Estas configurações são opcionais e devem ser implementadas conforme necessário. Sempre faça backup antes de fazer alterações no código.
