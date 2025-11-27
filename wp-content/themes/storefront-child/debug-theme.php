<?php
/**
 * Debug do tema - Verificar se está sendo carregado
 */

// Adicionar informações de debug ao footer
function debug_theme_info() {
    if (current_user_can('manage_options')) {
        echo '<div style="position: fixed; bottom: 0; left: 0; background: #000; color: #fff; padding: 10px; z-index: 9999; font-size: 12px;">';
        echo '<strong>DEBUG TEMA:</strong><br>';
        echo 'Tema Ativo: ' . wp_get_theme()->get('Name') . '<br>';
        echo 'Tema Pai: ' . wp_get_theme()->get('Template') . '<br>';
        echo 'Versão: ' . wp_get_theme()->get('Version') . '<br>';
        echo 'CSS Carregado: ' . (wp_style_is('storefront-child-style', 'enqueued') ? 'SIM' : 'NÃO') . '<br>';
        echo 'Timestamp: ' . time();
        echo '</div>';
    }
}
add_action('wp_footer', 'debug_theme_info');



