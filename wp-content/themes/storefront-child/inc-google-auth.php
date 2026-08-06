<?php
if (!defined('ABSPATH')) exit;

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

add_action('init', function () {
    add_rewrite_rule('^google-auth-callback/?$', 'index.php?eletronicos_google_cb=1', 'top');
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'eletronicos_google_cb';
    return $vars;
});

add_action('template_redirect', function () {
    if (!get_query_var('eletronicos_google_cb')) return;

    $login_url = wc_get_page_permalink('myaccount');

    $client_id     = get_option('eletronicos_google_client_id', '');
    $client_secret = get_option('eletronicos_google_client_secret', '');

    if (!$client_id || !$client_secret) {
        wp_redirect(add_query_arg('google_error', 'config', $login_url));
        exit;
    }

    $code  = sanitize_text_field(wp_unslash($_GET['code']  ?? ''));
    $state = sanitize_text_field(wp_unslash($_GET['state'] ?? ''));

    if (!$code || !wp_verify_nonce($state, 'eletronicos_google_state')) {
        wp_redirect(add_query_arg('google_error', 'state', $login_url));
        exit;
    }

    $token_res = wp_remote_post('https://oauth2.googleapis.com/token', [
        'body' => [
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => home_url('/google-auth-callback/'),
            'grant_type'    => 'authorization_code',
        ],
        'timeout' => 15,
    ]);

    if (is_wp_error($token_res)) {
        wp_redirect(add_query_arg('google_error', 'token', $login_url));
        exit;
    }

    $token = json_decode(wp_remote_retrieve_body($token_res), true);

    if (empty($token['access_token'])) {
        wp_redirect(add_query_arg('google_error', 'token', $login_url));
        exit;
    }

    $info_res = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', [
        'headers' => ['Authorization' => 'Bearer ' . $token['access_token']],
        'timeout' => 15,
    ]);

    if (is_wp_error($info_res)) {
        wp_redirect(add_query_arg('google_error', 'userinfo', $login_url));
        exit;
    }

    $info = json_decode(wp_remote_retrieve_body($info_res), true);

    if (empty($info['email']) || !$info['verified_email']) {
        wp_redirect(add_query_arg('google_error', 'email', $login_url));
        exit;
    }

    $email   = sanitize_email($info['email']);
    $g_id    = sanitize_text_field($info['id'] ?? '');
    $name    = sanitize_text_field($info['name'] ?? '');
    $first   = sanitize_text_field($info['given_name'] ?? '');
    $last    = sanitize_text_field($info['family_name'] ?? '');

    $user = get_user_by('email', $email);

    if (!$user) {
        $base = sanitize_user(strtolower(preg_replace('/\s+/', '.', $name ?: explode('@', $email)[0])));
        $base = $base ?: 'user';
        $slug = $base;
        $i    = 1;
        while (username_exists($slug)) {
            $slug = $base . $i++;
        }

        $user_id = wp_insert_user([
            'user_login'   => $slug,
            'user_email'   => $email,
            'display_name' => $name,
            'first_name'   => $first,
            'last_name'    => $last,
            'user_pass'    => wp_generate_password(24),
            'role'         => 'customer',
        ]);

        if (is_wp_error($user_id)) {
            wp_redirect(add_query_arg('google_error', 'create', $login_url));
            exit;
        }

        update_user_meta($user_id, '_eletronicos_google_id', $g_id);
        $user = get_user_by('id', $user_id);
    } else {
        update_user_meta($user->ID, '_eletronicos_google_id', $g_id);
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);
    do_action('wp_login', $user->user_login, $user);

    $redirect_after = get_transient('eletronicos_google_redirect_' . $state);
    delete_transient('eletronicos_google_redirect_' . $state);

    wp_redirect(wp_validate_redirect($redirect_after ?: wc_get_account_endpoint_url('dashboard'), wc_get_account_endpoint_url('dashboard')));
    exit;
});
