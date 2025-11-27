<?php
/**
 * Teste WordPress - Verificar se o tema está sendo carregado
 */

// Carregar WordPress
require_once('../../../wp-load.php');

// Verificar se o tema está ativo
$current_theme = wp_get_theme();
$parent_theme = wp_get_theme($current_theme->get('Template'));

echo "<h1>Teste WordPress - Storefront Child</h1>";
echo "<p><strong>Tema Ativo:</strong> " . $current_theme->get('Name') . "</p>";
echo "<p><strong>Tema Pai:</strong> " . $parent_theme->get('Name') . "</p>";
echo "<p><strong>Versão:</strong> " . $current_theme->get('Version') . "</p>";
echo "<p><strong>Diretório:</strong> " . get_stylesheet_directory() . "</p>";

// Verificar se o CSS está sendo enfileirado
echo "<h2>Verificação de CSS</h2>";
echo "<p><strong>CSS do tema pai:</strong> " . (wp_style_is('storefront-style', 'enqueued') ? 'SIM' : 'NÃO') . "</p>";
echo "<p><strong>CSS do tema filho:</strong> " . (wp_style_is('storefront-child-style', 'enqueued') ? 'SIM' : 'NÃO') . "</p>";

// Verificar se o arquivo CSS existe
$css_file = get_stylesheet_directory() . '/style.css';
echo "<p><strong>Arquivo CSS existe:</strong> " . (file_exists($css_file) ? 'SIM' : 'NÃO') . "</p>";
echo "<p><strong>Tamanho do arquivo:</strong> " . (file_exists($css_file) ? filesize($css_file) . ' bytes' : 'N/A') . "</p>";

// Verificar se há logo personalizada
$logo_id = get_theme_mod('custom_logo');
echo "<h2>Verificação de Logo</h2>";
echo "<p><strong>Logo personalizada:</strong> " . ($logo_id ? 'SIM (ID: ' . $logo_id . ')' : 'NÃO') . "</p>";

if ($logo_id) {
    $logo_url = wp_get_attachment_image_url($logo_id, 'full');
    echo "<p><strong>URL da logo:</strong> " . ($logo_url ? $logo_url : 'N/A') . "</p>";
    echo "<p><strong>Logo existe:</strong> " . ($logo_url && file_exists(str_replace(home_url(), ABSPATH, $logo_url)) ? 'SIM' : 'NÃO') . "</p>";
}

// Verificar se há logo do site
$site_logo = get_theme_mod('storefront_logo');
echo "<p><strong>Logo do Storefront:</strong> " . ($site_logo ? 'SIM' : 'NÃO') . "</p>";

// Verificar se há logo padrão
echo "<p><strong>Logo padrão (site-title):</strong> " . (get_bloginfo('name') ? 'SIM (' . get_bloginfo('name') . ')' : 'NÃO') . "</p>";

echo "<h2>Teste de CSS</h2>";
echo "<p>Se você conseguir ver este texto, o WordPress está funcionando.</p>";
echo "<p>Se o header tiver fundo vermelho claro, o CSS está sendo carregado.</p>";
echo "<p>Se a logo aparecer branca e centralizada, os estilos estão funcionando.</p>";
?>



