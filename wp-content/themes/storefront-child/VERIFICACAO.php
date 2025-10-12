<?php
/**
 * Arquivo de Verificação do Tema Storefront Child
 * 
 * Este arquivo verifica se todas as dependências e configurações
 * estão corretas para o funcionamento do tema.
 * 
 * @package Storefront_Child
 * @version 1.0.0
 */

// Evitar acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de verificação do tema
 */
class Storefront_Child_Verification {
    
    /**
     * Verificar se o tema pai está ativo
     */
    public static function check_parent_theme() {
        $parent_theme = wp_get_theme('storefront');
        
        if (!$parent_theme->exists()) {
            return array(
                'status' => 'error',
                'message' => 'Tema pai Storefront não encontrado. Instale o tema Storefront primeiro.'
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'Tema pai Storefront encontrado (versão: ' . $parent_theme->get('Version') . ')'
        );
    }
    
    /**
     * Verificar se o WooCommerce está ativo
     */
    public static function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            return array(
                'status' => 'error',
                'message' => 'WooCommerce não está ativo. Instale e ative o plugin WooCommerce.'
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'WooCommerce está ativo (versão: ' . WC()->version . ')'
        );
    }
    
    /**
     * Verificar arquivos do tema
     */
    public static function check_theme_files() {
        $required_files = array(
            'style.css',
            'functions.php',
            'woocommerce.php',
            'assets/js/child-theme.js',
            'assets/css/woocommerce.css',
            'languages/storefront-child-pt_BR.po'
        );
        
        $missing_files = array();
        $theme_path = get_stylesheet_directory();
        
        foreach ($required_files as $file) {
            if (!file_exists($theme_path . '/' . $file)) {
                $missing_files[] = $file;
            }
        }
        
        if (!empty($missing_files)) {
            return array(
                'status' => 'error',
                'message' => 'Arquivos faltando: ' . implode(', ', $missing_files)
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'Todos os arquivos do tema estão presentes'
        );
    }
    
    /**
     * Verificar configurações do WordPress
     */
    public static function check_wordpress_config() {
        $issues = array();
        
        // Verificar versão do WordPress
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            $issues[] = 'WordPress versão muito antiga. Recomendado: 5.0 ou superior.';
        }
        
        // Verificar versão do PHP
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $issues[] = 'PHP versão muito antiga. Recomendado: 7.4 ou superior.';
        }
        
        // Verificar se o tema filho está ativo
        if (get_template() !== 'storefront') {
            $issues[] = 'Tema pai não é o Storefront.';
        }
        
        if (!empty($issues)) {
            return array(
                'status' => 'warning',
                'message' => implode(' ', $issues)
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'Configurações do WordPress estão corretas'
        );
    }
    
    /**
     * Verificar funcionalidades do tema
     */
    public static function check_theme_features() {
        $features = array();
        
        // Verificar suporte a WooCommerce
        if (current_theme_supports('woocommerce')) {
            $features[] = 'Suporte ao WooCommerce ativo';
        } else {
            $features[] = 'Suporte ao WooCommerce não ativo';
        }
        
        // Verificar suporte a post thumbnails
        if (current_theme_supports('post-thumbnails')) {
            $features[] = 'Suporte a post thumbnails ativo';
        } else {
            $features[] = 'Suporte a post thumbnails não ativo';
        }
        
        // Verificar suporte a HTML5
        if (current_theme_supports('html5')) {
            $features[] = 'Suporte a HTML5 ativo';
        } else {
            $features[] = 'Suporte a HTML5 não ativo';
        }
        
        return array(
            'status' => 'info',
            'message' => implode(', ', $features)
        );
    }
    
    /**
     * Executar todas as verificações
     */
    public static function run_all_checks() {
        $checks = array(
            'parent_theme' => self::check_parent_theme(),
            'woocommerce' => self::check_woocommerce(),
            'theme_files' => self::check_theme_files(),
            'wordpress_config' => self::check_wordpress_config(),
            'theme_features' => self::check_theme_features()
        );
        
        return $checks;
    }
    
    /**
     * Exibir resultados das verificações
     */
    public static function display_verification_results() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $checks = self::run_all_checks();
        
        echo '<div class="storefront-child-verification">';
        echo '<h2>🔍 Verificação do Tema Storefront Child</h2>';
        
        foreach ($checks as $check_name => $result) {
            $status_class = 'status-' . $result['status'];
            $status_icon = self::get_status_icon($result['status']);
            
            echo '<div class="verification-item ' . $status_class . '">';
            echo '<span class="status-icon">' . $status_icon . '</span>';
            echo '<span class="check-name">' . ucfirst(str_replace('_', ' ', $check_name)) . ':</span>';
            echo '<span class="check-message">' . $result['message'] . '</span>';
            echo '</div>';
        }
        
        echo '</div>';
        
        // Adicionar estilos
        echo '<style>
        .storefront-child-verification {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .storefront-child-verification h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .verification-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .status-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .status-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .status-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .status-icon {
            margin-right: 10px;
            font-size: 1.2em;
        }
        .check-name {
            font-weight: 600;
            margin-right: 10px;
        }
        </style>';
    }
    
    /**
     * Obter ícone baseado no status
     */
    private static function get_status_icon($status) {
        switch ($status) {
            case 'success':
                return '✅';
            case 'error':
                return '❌';
            case 'warning':
                return '⚠️';
            case 'info':
                return 'ℹ️';
            default:
                return '❓';
        }
    }
}

/**
 * Adicionar verificação ao admin
 */
function storefront_child_add_verification_to_admin() {
    if (current_user_can('manage_options')) {
        add_action('admin_notices', array('Storefront_Child_Verification', 'display_verification_results'));
    }
}
add_action('admin_init', 'storefront_child_add_verification_to_admin');

/**
 * Adicionar página de verificação ao menu admin
 */
function storefront_child_add_verification_page() {
    add_theme_page(
        'Verificação do Tema',
        'Verificação do Tema',
        'manage_options',
        'storefront-child-verification',
        'storefront_child_verification_page_callback'
    );
}
add_action('admin_menu', 'storefront_child_add_verification_page');

/**
 * Callback da página de verificação
 */
function storefront_child_verification_page_callback() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    echo '<div class="wrap">';
    echo '<h1>🔍 Verificação do Tema Storefront Child</h1>';
    
    Storefront_Child_Verification::display_verification_results();
    
    echo '<h3>📋 Informações do Tema</h3>';
    echo '<table class="widefat">';
    echo '<tr><td><strong>Nome do Tema:</strong></td><td>' . wp_get_theme()->get('Name') . '</td></tr>';
    echo '<tr><td><strong>Versão:</strong></td><td>' . wp_get_theme()->get('Version') . '</td></tr>';
    echo '<tr><td><strong>Tema Pai:</strong></td><td>' . wp_get_theme()->get('Template') . '</td></tr>';
    echo '<tr><td><strong>Autor:</strong></td><td>' . wp_get_theme()->get('Author') . '</td></tr>';
    echo '<tr><td><strong>Descrição:</strong></td><td>' . wp_get_theme()->get('Description') . '</td></tr>';
    echo '</table>';
    
    echo '</div>';
}
