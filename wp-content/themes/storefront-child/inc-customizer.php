<?php
add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('promo_banner', [
        'title'    => 'Banner Promocional (Home)',
        'priority' => 120,
    ]);

    $wp_customize->add_setting('promo_banner_image', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'promo_banner_image', [
        'label'   => 'Imagem de fundo',
        'section' => 'promo_banner',
    ]));

    $wp_customize->add_setting('promo_banner_title', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('promo_banner_title', ['label' => 'Título', 'section' => 'promo_banner', 'type' => 'text']);

    $wp_customize->add_setting('promo_banner_subtitle', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('promo_banner_subtitle', ['label' => 'Subtítulo', 'section' => 'promo_banner', 'type' => 'text']);

    $wp_customize->add_setting('promo_banner_btn_text', ['default' => 'Ver ofertas', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('promo_banner_btn_text', ['label' => 'Texto do botão', 'section' => 'promo_banner', 'type' => 'text']);

    $wp_customize->add_setting('promo_banner_btn_link', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('promo_banner_btn_link', ['label' => 'Link do botão', 'section' => 'promo_banner', 'type' => 'url']);

    $wp_customize->add_section('contact_info', [
        'title'    => 'Informações de Contato',
        'priority' => 130,
    ]);

    $wp_customize->add_setting('contact_phone', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('contact_phone', ['label' => 'Telefone', 'section' => 'contact_info', 'type' => 'text']);

    $wp_customize->add_setting('contact_whatsapp', ['default' => '', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('contact_whatsapp', ['label' => 'WhatsApp', 'section' => 'contact_info', 'type' => 'text']);

    $wp_customize->add_setting('contact_whatsapp_message', ['default' => 'Olá! Gostaria de saber mais sobre os produtos.', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('contact_whatsapp_message', ['label' => 'Mensagem padrão do WhatsApp', 'section' => 'contact_info', 'type' => 'text']);

    $wp_customize->add_setting('contact_address', ['default' => '', 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('contact_address', ['label' => 'Endereço da loja', 'section' => 'contact_info', 'type' => 'textarea']);

    $wp_customize->add_setting('contact_maps_url', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('contact_maps_url', ['label' => 'Link do Google Maps', 'section' => 'contact_info', 'type' => 'url']);

    $wp_customize->add_section('google_oauth', [
        'title'       => 'Login com Google (OAuth)',
        'description' => 'Credenciais do Google Cloud Console. URI de redirecionamento autorizado: ' . home_url('/google-auth-callback/'),
        'priority'    => 200,
    ]);

    $wp_customize->add_setting('eletronicos_google_client_id_preview', ['default' => '', 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('eletronicos_google_client_id_preview', [
        'label'       => 'Client ID',
        'description' => 'Salvo em wp_options, não no tema.',
        'section'     => 'google_oauth',
        'type'        => 'text',
    ]);

    $wp_customize->add_setting('eletronicos_google_client_secret_preview', ['default' => '', 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('eletronicos_google_client_secret_preview', [
        'label'   => 'Client Secret',
        'section' => 'google_oauth',
        'type'    => 'text',
    ]);
});

// Persist Google OAuth credentials into wp_options (not theme_mods) on save
add_action('customize_save_after', function ($manager) {
    $id     = $manager->get_setting('eletronicos_google_client_id_preview');
    $secret = $manager->get_setting('eletronicos_google_client_secret_preview');

    if ($id) {
        $val = sanitize_text_field($id->post_value());
        if ($val !== '') update_option('eletronicos_google_client_id', $val);
    }

    if ($secret) {
        $val = sanitize_text_field($secret->post_value());
        if ($val !== '') update_option('eletronicos_google_client_secret', $val);
    }
});
