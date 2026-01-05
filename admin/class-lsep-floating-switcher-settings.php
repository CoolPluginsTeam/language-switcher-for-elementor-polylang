<?php
/**
 * Floating Language Switcher Settings
 * Standalone admin page (like TranslatePress)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LSEP_Floating_Switcher_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 25 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'wp_ajax_lsep_save_floating_switcher', [ $this, 'ajax_save_settings' ] );
    }
    
    /**
     * Add standalone admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'lsep-settings', // Parent slug (your existing dashboard)
            __( 'Floating Switcher', 'language-switcher-for-elementor-polylang' ),
            __( 'Floating Switcher', 'language-switcher-for-elementor-polylang' ),
            'manage_options',
            'lsep-floating-switcher',
            [ $this, 'render_page' ]
        );
    }
    
    /**
     * Render the settings page
     */
    public function render_page() {
        require_once plugin_dir_path( __FILE__ ) . 'dashboard/partials/floating-switcher-page.php';
    }
    
    /**
     * Enqueue assets only on our page
     */
    public function enqueue_assets( $hook ) {   
        
        // Check if current hook matches any valid hook
        $is_our_page = strpos( $hook, 'lsep-floating-switcher' ) !== false;
        
        // Also check by page parameter
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking current admin page, not processing form data
        if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'lsep-floating-switcher' ) {
            $is_our_page = true;
        }
        
        if ( ! $is_our_page ) {
            return;
        }

        $plugin_url = LSEP_PLUGIN_URL;
        $version    = defined( 'LSEP_VERSION' ) ? LSEP_VERSION : '1.0.0';
        
        // Enqueue WordPress React
        wp_enqueue_script( 'wp-element' );
        wp_enqueue_script( 'wp-i18n' );
        
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
            [ 'wp-element', 'wp-i18n' ],
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
        $config    = $this->get_switcher_config();
        $languages = $this->get_polylang_languages();
        
        return [
            'config'    => $config,
            'languages' => $languages,
            'nonce'     => wp_create_nonce( 'lsep_floating_switcher_save' ),
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'pluginUrl' => LSEP_PLUGIN_URL,
            'flagsPath' => $this->get_flags_path(),
        ];
    }
    
    /**
     * Get Polylang languages
     */
    private function get_polylang_languages() {
        if ( ! function_exists( 'pll_languages_list' ) ) {
            return $this->get_default_languages();
        }
        
        $languages     = [];
        $pll_languages = pll_languages_list( [ 'fields' => false ] );
        
        if ( empty( $pll_languages ) ) {
            return $this->get_default_languages();
        }
        
        foreach ( $pll_languages as $lang ) {
            $languages[] = [
                'code'   => $lang->slug,
                'name'   => $lang->name,
                'flag'   => \LSEP_HELPERS::get_plugin_flag_url( $lang->flag_url ?? '' ),
                'locale' => $lang->locale,
            ];
        }
        
        return $languages;
    }

    /**
     * Get default languages (English and French) when Polylang is not configured
     */
    private function get_default_languages() {
        $plugin_url = LSEP_PLUGIN_URL;
        
        return [
            [
                'code'   => 'en',
                'name'   => __( 'English', 'language-switcher-for-elementor-polylang' ),
                'flag'   => $plugin_url . 'assets/flags/us.svg',
                'locale' => 'en_US',
            ],
            [
                'code'   => 'fr',
                'name'   => __( 'Français', 'language-switcher-for-elementor-polylang' ),
                'flag'   => $plugin_url . 'assets/flags/fr.svg',
                'locale' => 'fr_FR',
            ],
        ];
    }
    
    /**
     * Get flags path
     */
    private function get_flags_path() {
        if ( defined( 'POLYLANG_DIR' ) ) {
            return content_url( 'plugins/polylang/flags/' );
        }
        return '';
    }
    
    /**
     * Get or initialize config
     */
    public function get_switcher_config() {
        $saved    = get_option( 'lsep_floating_switcher_config', null );
        $defaults = $this->get_default_config();
        
        if ( ! is_array( $saved ) || empty( $saved ) ) {
            update_option( 'lsep_floating_switcher_config', $defaults );
            return $defaults;
        }
        
        return $this->deep_merge_defaults( $saved, $defaults );
    }
    
    /**
     * Get default config
     */
    public function get_default_config() {
        $layout_defaults = [
            'desktop' => [
                'position'         => 'bottom-right',
                'width'            => 'default',
                'customWidth'      => 216,
                'padding'          => 'default',
                'customPadding'    => 0,
                'flagIconPosition' => 'before',
                'languageNames'    => 'full',
            ],
            'mobile'  => [
                'position'         => 'bottom-right',
                'width'            => 'default',
                'customWidth'      => 216,
                'padding'          => 'default',
                'customPadding'    => 0,
                'flagIconPosition' => 'before',
                'languageNames'    => 'full',
            ],
        ];
        
        return [
            'enabled'           => false,
            'type'              => 'dropdown',
            'bgColor'           => '#ffffff',
            'bgHoverColor'      => '#0000000d',
            'textColor'         => '#143852',
            'textHoverColor'    => '#1d2327',
            'borderColor'       => '#1438521a',
            'borderWidth'       => 1,
            'borderRadius'      => [ 8, 8, 0, 0 ],
            'size'              => 'normal',
            'flagShape'         => 'rect',
            'flagRadius'        => 2,
            'enableCustomCss'   => true,
            'customCss'         => '',
            'layoutCustomizer'  => $layout_defaults,
            'enableTransitions' => true,
        ];
    }
    
    /**
     * Deep merge with defaults
     */
    private function deep_merge_defaults( $saved, $defaults ) {
        foreach ( $defaults as $key => $default_value ) {
            if ( ! array_key_exists( $key, $saved ) ) {
                $saved[ $key ] = $default_value;
            } elseif ( is_array( $default_value ) && is_array( $saved[ $key ] ) ) {
                $saved[ $key ] = $this->deep_merge_defaults( $saved[ $key ], $default_value );
            }
        }
        return $saved;
    }
    
    /**
     * AJAX: Save settings
     */
    public function ajax_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'language-switcher-for-elementor-polylang' ), 403 );
        }
        
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'lsep_floating_switcher_save' ) ) {
            wp_send_json_error( __( 'Invalid nonce.', 'language-switcher-for-elementor-polylang' ), 403 );
        }
        
        $config_raw = isset( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : '{}';
        $config     = json_decode( $config_raw, true );
        
        if ( ! is_array( $config ) ) {
            wp_send_json_error( __( 'Invalid data.', 'language-switcher-for-elementor-polylang' ), 400 );
        }
        
        $sanitized = $this->sanitize_config( $config );
        update_option( 'lsep_floating_switcher_config', $sanitized );
        
        wp_send_json_success( __( 'Settings saved successfully.', 'language-switcher-for-elementor-polylang' ) );
    }
    
   /**
 * Sanitize config with proper validation for each field
 */
private function sanitize_config( $config ) {
    $sanitized = [];
    
    // Boolean fields
    $sanitized['enabled'] = ! empty( $config['enabled'] );
    $sanitized['enableCustomCss'] = ! empty( $config['enableCustomCss'] );
    $sanitized['enableTransitions'] = ! empty( $config['enableTransitions'] );
    
    // Type field (must be dropdown or inline)
    $sanitized['type'] = in_array( $config['type'] ?? '', [ 'dropdown', 'inline', 'side-by-side' ], true ) 
    ? $config['type'] 
    : 'dropdown';
    
    // Color fields (hex colors with alpha)
    $color_fields = [ 'bgColor', 'bgHoverColor', 'textColor', 'textHoverColor', 'borderColor' ];
    foreach ( $color_fields as $field ) {
        $sanitized[ $field ] = $this->sanitize_color( $config[ $field ] ?? '#ffffff' );
    }
    
    // Numeric fields
    $sanitized['borderWidth'] = absint( $config['borderWidth'] ?? 1 );
    $sanitized['flagRadius'] = absint( $config['flagRadius'] ?? 2 );
    
    // Border radius array (4 values)
    if ( isset( $config['borderRadius'] ) && is_array( $config['borderRadius'] ) ) {
        $sanitized['borderRadius'] = array_map( 'absint', array_slice( $config['borderRadius'], 0, 4 ) );
        // Ensure we have exactly 4 values
        $sanitized['borderRadius'] = array_pad( $sanitized['borderRadius'], 4, 0 );
    } else {
        $sanitized['borderRadius'] = [ 8, 8, 0, 0 ];
    }
    
    // Size field (normal or large)
    $sanitized['size'] = in_array( $config['size'] ?? '', [ 'normal', 'large' ], true )
        ? $config['size']
        : 'normal';
    
    // Flag shape (rect, square, rounded)
    $sanitized['flagShape'] = in_array( $config['flagShape'] ?? '', [ 'rect', 'square', 'rounded' ], true )
        ? $config['flagShape']
        : 'rect';
    
    // Custom CSS (strip tags and scripts)
    $sanitized['customCss'] = '';
if ( ! empty( $config['customCss'] ) ) {
    // Remove script tags and dangerous patterns
    $custom_css = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $config['customCss'] );
    $custom_css = preg_replace( '/<style\b[^>]*>.*?<\/style>/is', '', $custom_css );
    $custom_css = wp_kses( $custom_css, array() ); // Strip all HTML tags
    $sanitized['customCss'] = sanitize_textarea_field( $custom_css );
}
    
    // Layout customizer
    $sanitized['layoutCustomizer'] = [];
    foreach ( [ 'desktop', 'mobile' ] as $device ) {
        if ( isset( $config['layoutCustomizer'][ $device ] ) && is_array( $config['layoutCustomizer'][ $device ] ) ) {
            $layout = $config['layoutCustomizer'][ $device ];
            
            // Position (e.g., 'bottom-right', 'top-left')
            $valid_positions = [
                'bottom-right', 'bottom-left', 'bottom-center',
                'top-right', 'top-left', 'top-center'
            ];
            $sanitized['layoutCustomizer'][ $device ]['position'] = in_array( $layout['position'] ?? '', $valid_positions, true )
                ? $layout['position']
                : 'bottom-right';
            
            // Width (default or custom)
            $sanitized['layoutCustomizer'][ $device ]['width'] = in_array( $layout['width'] ?? '', [ 'default', 'custom' ], true )
                ? $layout['width']
                : 'default';
            $sanitized['layoutCustomizer'][ $device ]['customWidth'] = absint( $layout['customWidth'] ?? 216 );
            
            // Padding (default or custom)
            $sanitized['layoutCustomizer'][ $device ]['padding'] = in_array( $layout['padding'] ?? '', [ 'default', 'custom' ], true )
                ? $layout['padding']
                : 'default';
            $sanitized['layoutCustomizer'][ $device ]['customPadding'] = absint( $layout['customPadding'] ?? 0 );
            
            // Flag position (before or after)
            $sanitized['layoutCustomizer'][ $device ]['flagIconPosition'] = in_array( $layout['flagIconPosition'] ?? '', [ 'before', 'after' ], true )
                ? $layout['flagIconPosition']
                : 'before';
            
            // Language names (full, short, none)
            $sanitized['layoutCustomizer'][ $device ]['languageNames'] = in_array( $layout['languageNames'] ?? '', [ 'full', 'short', 'none' ], true )
                ? $layout['languageNames']
                : 'full';
        } else {
            // Use defaults
            $sanitized['layoutCustomizer'][ $device ] = $this->get_default_config()['layoutCustomizer'][ $device ];
        }
    }
    
    return $sanitized;
}

/**
 * Sanitize color value (supports hex with alpha)
 */
private function sanitize_color( $color ) {
    // Remove whitespace
    $color = trim( $color );
    
    // Allow hex colors with 3, 6, or 8 characters (with alpha)
    if ( preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color ) ) {
        return $color;
    }
    
    // Allow rgba colors
    if ( preg_match( '/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/', $color ) ) {
        return $color;
    }
    
    // Default fallback
    return '#ffffff';
}
}