<?php
/**
 * Floating Language Switcher Settings
 * Standalone admin page (like TranslatePress)
 */

if (!defined('ABSPATH')) {
    exit;
}

class LSEP_Floating_Switcher_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu'], 25);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_lsep_save_floating_switcher', [$this, 'ajax_save_settings']);
    }
    
    /**
     * Add standalone admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'lsep-settings', // Parent slug (your existing dashboard)
            __('Floating Switcher', 'language-switcher-for-elementor-polylang'),
            __('Floating Switcher', 'language-switcher-for-elementor-polylang'),
            'manage_options',
            'lsep-floating-switcher',
            [$this, 'render_page']
        );
    }
    
    /**
     * Render the settings page
     */
    public function render_page() {
        require_once plugin_dir_path(__FILE__) . 'dashboard/partials/floating-switcher-page.php';
    }
    
   /**
 * Enqueue assets only on our page
 */
public function enqueue_assets($hook) {   

    $valid_hooks = [
        'lsep-settings_page_lsep-floating-switcher',
        'admin_page_lsep-floating-switcher',
        'lsep_page_lsep-floating-switcher',
        'toplevel_page_lsep-floating-switcher',
    ];
    
    // Check if current hook matches any valid hook
    $is_our_page = false;
    foreach ($valid_hooks as $valid_hook) {
        if (strpos($hook, 'lsep-floating-switcher') !== false) {
            $is_our_page = true;
            break;
        }
    }
    
// Also check by page parameter
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking current admin page, not processing form data
if (isset($_GET['page']) && sanitize_text_field(wp_unslash($_GET['page'])) === 'lsep-floating-switcher') {
    $is_our_page = true;
}
    
    if (!$is_our_page) {
        return;
    }

    
    $plugin_url = plugin_dir_url(dirname(__FILE__));
    $version = time();
    
    // Enqueue WordPress React
    wp_enqueue_script('wp-element');
    wp_enqueue_script('wp-i18n');
    
    // Enqueue CSS
    wp_enqueue_style(
        'lsep-floating-switcher-admin',
        $plugin_url . 'admin/dashboard/includes/css/lsep-floating-switcher-admin.css',
        [],
        $version
    );
    
    // Enqueue Vue/React app
    wp_enqueue_script(
        'lsep-floating-switcher-app',
        $plugin_url . 'admin/dashboard/includes/js/lsep-floating-switcher-app.js',
        ['wp-element', 'wp-i18n'],
        $version,
        true
    );
    
    // Pass data to app
    wp_localize_script(
        'lsep-floating-switcher-app',
        'lsepFloaterData',
        $this->get_localized_data()
    );
    
    wp_set_script_translations(
        'lsep-floating-switcher-app',
        'language-switcher-for-elementor-polylang'
    );
}
    
    /**
     * Get data for Vue app
     */
    private function get_localized_data() {
        $config = $this->get_switcher_config();
        $languages = $this->get_polylang_languages();
        
        return [
            'config' => $config,
            'languages' => $languages,
            'nonce' => wp_create_nonce('lsep_floating_switcher_save'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => plugin_dir_url(dirname(__FILE__)),
            'flagsPath' => $this->get_flags_path(),
        ];
    }
    
    /**
     * Get Polylang languages
     */
    private function get_polylang_languages() {
        if (!function_exists('pll_languages_list')) {
            return [];
        }
        
        $languages = [];
        $pll_languages = pll_languages_list(['fields' => false]);
        
        if (empty($pll_languages)) {
            return [];
        }
        
        foreach ($pll_languages as $lang) {
            $languages[] = [
                'code' => $lang->slug,
                'name' => $lang->name,
                'flag' => $this->get_plugin_flag_url($lang->flag_url ?? ''),
                'locale' => $lang->locale,
            ];
        }
        
        return $languages;
    }
    
    /**
     * Convert Polylang PNG flag URL to plugin's SVG flag URL
     */
    private function get_plugin_flag_url($polylang_flag_url) {
        // Get flag code from Polylang URL using the helper function
        $flag_code = \LSEP_HELPERS::lsep_get_flag_code($polylang_flag_url);
        
        if (empty($flag_code)) {
            return $polylang_flag_url; // Fallback to original
        }
        
        // Build path to plugin's SVG flag
        $plugin_url = plugin_dir_url(dirname(__FILE__));
        return $plugin_url . 'assets/flags/' . $flag_code . '.svg';
    }
    
    /**
     * Get flags path
     */
    private function get_flags_path() {
        if (defined('POLYLANG_DIR')) {
            return content_url('plugins/polylang/flags/');
        }
        return '';
    }
    
    /**
     * Get or initialize config
     */
    public function get_switcher_config() {
        $saved = get_option('lsep_floating_switcher_config', null);
        $defaults = $this->get_default_config();
        
        if (!is_array($saved) || empty($saved)) {
            update_option('lsep_floating_switcher_config', $defaults);
            return $defaults;
        }
        
        return $this->deep_merge_defaults($saved, $defaults);
    }
    
    /**
     * Get default config
     */
    public function get_default_config() {
        $layout_defaults = [
            'desktop' => [
                'position' => 'bottom-right',
                'width' => 'default',
                'customWidth' => 216,
                'padding' => 'default',
                'customPadding' => 0,
                'flagIconPosition' => 'before',
                'languageNames' => 'full',
            ],
            'mobile' => [
                'position' => 'bottom-right',
                'width' => 'default',
                'customWidth' => 216,
                'padding' => 'default',
                'customPadding' => 0,
                'flagIconPosition' => 'before',
                'languageNames' => 'full',
            ],
        ];
        
        return [
            'enabled' => true,
            'type' => 'dropdown',
            'bgColor' => '#ffffff',
            'bgHoverColor' => '#0000000d',
            'textColor' => '#143852',
            'textHoverColor' => '#1d2327',
            'borderColor' => '#1438521a',
            'borderWidth' => 1,
            'borderRadius' => [8, 8, 0, 0],
            'size' => 'normal',
            'flagShape' => 'rect',
            'flagRadius' => 2,
            'enableCustomCss' => false,
            'customCss' => '',
            'oppositeLanguage' => false,
            'showPoweredBy' => false,
            'layoutCustomizer' => $layout_defaults,
            'enableTransitions' => true,
        ];
    }
    
    /**
     * Deep merge with defaults
     */
    private function deep_merge_defaults($saved, $defaults) {
        foreach ($defaults as $key => $default_value) {
            if (!array_key_exists($key, $saved)) {
                $saved[$key] = $default_value;
            } elseif (is_array($default_value) && is_array($saved[$key])) {
                $saved[$key] = $this->deep_merge_defaults($saved[$key], $default_value);
            }
        }
        return $saved;
    }
    
    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'language-switcher-for-elementor-polylang'), 403);
        }
        
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'lsep_floating_switcher_save')) {
            wp_send_json_error(__('Invalid nonce.', 'language-switcher-for-elementor-polylang'), 403);
        }
        
        $config_raw = isset($_POST['config']) ? sanitize_text_field(wp_unslash($_POST['config'])) : '{}';
        $config = json_decode($config_raw, true);
        
        if (!is_array($config)) {
            wp_send_json_error(__('Invalid data.', 'language-switcher-for-elementor-polylang'), 400);
        }
        
        $sanitized = $this->sanitize_config($config);
        update_option('lsep_floating_switcher_config', $sanitized);
        
        wp_send_json_success(__('Settings saved successfully.', 'language-switcher-for-elementor-polylang'));
    }
    
    /**
     * Sanitize config (keeping it simple - add full validation later)
     */
    private function sanitize_config($config) {
        // Add full sanitization here based on previous code
        // For now, basic sanitization
        return $config;
    }
}