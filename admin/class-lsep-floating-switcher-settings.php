<?php
/**
 * Floating Language Switcher Settings
 *
 * Handles the admin settings page for the floating language switcher feature.
 * Provides a comprehensive interface for configuring the floating switcher's
 * appearance, behavior, and layout settings.
 *
 * @package    Language_Switcher_For_Elementor_Polylang
 * @subpackage Language_Switcher_For_Elementor_Polylang/admin
 * @since      1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * LSEP Floating Switcher Settings Class
 *
 * Manages the admin interface for floating language switcher configuration.
 * Implements singleton pattern to ensure only one instance exists.
 *
 * @since 1.2.4
 */
class LSEP_Floating_Switcher_Settings {
    
    /**
     * Singleton instance
     *
     * @since 1.2.4
     * @var LSEP_Floating_Switcher_Settings|null
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * Returns the single instance of this class, creating it if necessary.
     *
     * @since 1.2.4
     * @return LSEP_Floating_Switcher_Settings The singleton instance
     */
    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     *
     * Private constructor to enforce singleton pattern.
     * Registers WordPress hooks for admin menu, assets, and AJAX handlers.
     *
     * @since 1.2.4
     */
    private function __construct() {
        // Add admin menu page
        add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 25 );
        
        // Enqueue admin assets (CSS/JS)
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        
        // Register AJAX handler for saving settings
        add_action( 'wp_ajax_lsep_save_floating_switcher', [ $this, 'ajax_save_settings' ] );
    }
    
    /**
     * Add Admin Menu Page
     *
     * Registers a submenu page under the main LSEP settings menu
     * for configuring the floating language switcher.
     *
     * @since 1.2.4
     */
    public function add_admin_menu() {
        add_submenu_page(
            'lsep-settings', // Parent slug (main LSEP dashboard)
            __( 'Floating Switcher', 'language-switcher-for-elementor-polylang' ), // Page title
            __( 'Floating Switcher', 'language-switcher-for-elementor-polylang' ), // Menu title
            'manage_options', // Capability required
            'lsep-floating-switcher', // Menu slug
            [ $this, 'render_page' ] // Callback function
        );
    }
    
    /**
     * Render Settings Page
     *
     * Outputs the HTML for the floating switcher settings page.
     * Includes the React app root element and page template.
     *
     * @since 1.2.4
     */
    public function render_page() {
        require_once plugin_dir_path( __FILE__ ) . 'dashboard/partials/floating-switcher-page.php';
    }
    
    /**
     * Enqueue Admin Assets
     *
     * Loads CSS and JavaScript files only on the floating switcher settings page.
     * Includes WordPress React libraries and localized data for the app.
     *
     * @since 1.2.4
     * @param string $hook The current admin page hook
     */
    public function enqueue_assets( $hook ) {   
        
        // Check if current hook matches our page
        $is_our_page = strpos( $hook, 'lsep-floating-switcher' ) !== false;
        
        // Also check by page parameter for additional verification
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking current admin page, not processing form data
        if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) === 'lsep-floating-switcher' ) {
            $is_our_page = true;
        }
        
        // Only enqueue on our settings page
        if ( ! $is_our_page ) {
            return;
        }

        $plugin_url = LSEP_PLUGIN_URL;
        $version    = defined( 'LSEP_VERSION' ) ? LSEP_VERSION : '1.0.0';
        
        // Enqueue WordPress React libraries (required for the app)
        wp_enqueue_script( 'wp-element' );
        wp_enqueue_script( 'wp-i18n' );
        
        // Enqueue admin stylesheet
        wp_enqueue_style(
            'lsep-floating-switcher-admin',
            $plugin_url . 'admin/dashboard/includes/css/lsep-floating-switcher-admin.css',
            [],
            $version
        );
        
        // Enqueue React app JavaScript
        wp_enqueue_script(
            'lsep-floating-switcher-app',
            $plugin_url . 'admin/dashboard/includes/js/lsep-floating-switcher-app.js',
            [ 'wp-element', 'wp-i18n' ], // Dependencies
            $version,
            true // Load in footer
        );
        
        // Pass configuration data and settings to the JavaScript app
        wp_localize_script(
            'lsep-floating-switcher-app',
            'lsepFloaterData',
            $this->get_localized_data()
        );
        
        // Set up script translations for internationalization
        wp_set_script_translations(
            'lsep-floating-switcher-app',
            'language-switcher-for-elementor-polylang',
             LSEP_PLUGIN_DIR . 'languages'
        );
    }
    
    /**
     * Get Localized Data
     *
     * Prepares data to be passed to the JavaScript app including
     * configuration, languages, nonce, and plugin paths.
     *
     * @since 1.2.4
     * @return array Array of data for JavaScript app
     */
    private function get_localized_data() {
        // Get current configuration and available languages
        $config    = $this->get_switcher_config();
        $languages = $this->get_polylang_languages();
        
        return [
            'config'    => $config, // Current switcher configuration
            'languages' => $languages, // Available Polylang languages
            'nonce'     => wp_create_nonce( 'lsep_floating_switcher_save' ), // Security nonce for AJAX
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ), // WordPress AJAX endpoint
            'pluginUrl' => LSEP_PLUGIN_URL, // Plugin base URL
            'flagsPath' => $this->get_flags_path(), // Path to flag images
        ];
    }
    
    /**
     * Get Polylang Languages
     *
     * Retrieves the list of languages configured in Polylang.
     * Falls back to default languages if Polylang is not active or configured.
     *
     * @since 1.2.4
     * @return array Array of language objects with code, name, flag, and locale
     */
    private function get_polylang_languages() {
        // Check if Polylang is active
        if ( ! function_exists( 'pll_languages_list' ) ) {
            return $this->get_default_languages();
        }
        
        $languages     = [];
        $pll_languages = pll_languages_list( [ 'fields' => false ] );
        
        // Use default languages if none configured
        if ( empty( $pll_languages ) ) {
            return $this->get_default_languages();
        }
        
        // Build language array from Polylang data
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
     * Get Default Languages
     *
     * Returns a default set of languages (English and French) to use
     * when Polylang is not configured or active.
     *
     * @since 1.2.4
     * @return array Array of default language objects
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
     * Get Flags Path
     *
     * Returns the URL path to the flag images directory.
     * Uses Polylang's flag directory if available.
     *
     * @since 1.2.4
     * @return string URL path to flags directory
     */
    private function get_flags_path() {
        if ( defined( 'POLYLANG_DIR' ) ) {
            return content_url( 'plugins/polylang/flags/' );
        }
        return '';
    }
    
    /**
     * Get Switcher Configuration
     *
     * Retrieves the saved floating switcher configuration from the database.
     * If no configuration exists or is invalid, returns and saves default configuration.
     * Ensures all required fields exist by merging with defaults.
     *
     * @since 1.2.4
     * @return array Complete switcher configuration array
     */
    public function get_switcher_config() {
        // Get saved configuration from database
        $saved    = get_option( 'lsep_floating_switcher_config', null );
        $defaults = $this->get_default_config();
        
        // If no saved config or invalid, initialize with defaults
        if ( ! is_array( $saved ) || empty( $saved ) ) {
            update_option( 'lsep_floating_switcher_config', $defaults );
            return $defaults;
        }
        
        // Merge saved config with defaults to ensure all fields exist
        return $this->deep_merge_defaults( $saved, $defaults );
    }
    
    /**
     * Get Default Configuration
     *
     * Returns the default configuration array for the floating switcher
     * with all settings set to their default values.
     *
     * @since 1.2.4
     * @return array Default configuration array
     */
    public function get_default_config() {
        // Device-specific layout defaults (desktop and mobile)
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
            'enabled'           => false, // Switcher disabled by default
            'type'              => 'dropdown', // Dropdown mode by default
            'bgColor'           => '#ffffff', // Background color
            'bgHoverColor'      => '#0000000d', // Hover background color
            'textColor'         => '#143852', // Text color
            'textHoverColor'    => '#1d2327', // Hover text color
            'borderColor'       => '#1438521a', // Border color
            'borderWidth'       => 1, // Border width in pixels
            'borderRadius'      => [ 8, 8, 0, 0 ], // Border radius for each corner [TL, TR, BR, BL]
            'size'              => 'normal', // Font and flag size
            'flagShape'         => 'rect', // Flag aspect ratio
            'flagRadius'        => 2, // Flag border radius
            'enableCustomCss'   => true, // Allow custom CSS
            'customCss'         => '', // Custom CSS code
            'layoutCustomizer'  => $layout_defaults, // Device-specific layout settings
            'enableTransitions' => true, // Enable animations
        ];
    }
    
    /**
     * Deep Merge with Defaults
     *
     * Recursively merges saved configuration with default values
     * to ensure all required keys exist even if they were added in updates.
     *
     * @since 1.2.4
     * @param array $saved    Saved configuration array
     * @param array $defaults Default configuration array
     * @return array Merged configuration array
     */
    private function deep_merge_defaults( $saved, $defaults ) {
        // Loop through defaults and add missing keys
        foreach ( $defaults as $key => $default_value ) {
            if ( ! array_key_exists( $key, $saved ) ) {
                // Add missing key with default value
                $saved[ $key ] = $default_value;
            } elseif ( is_array( $default_value ) && is_array( $saved[ $key ] ) ) {
                // Recursively merge nested arrays
                $saved[ $key ] = $this->deep_merge_defaults( $saved[ $key ], $default_value );
            }
        }
        return $saved;
    }
    
    /**
     * AJAX Handler: Save Settings
     *
     * Handles the AJAX request to save floating switcher configuration.
     * Validates permissions, nonce, and data before saving to database.
     *
     * @since 1.2.4
     */
    public function ajax_save_settings() {
        // Check user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'language-switcher-for-elementor-polylang' ), 403 );
        }
        
        // Verify nonce for security
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'lsep_floating_switcher_save' ) ) {
            wp_send_json_error( __( 'Invalid nonce.', 'language-switcher-for-elementor-polylang' ), 403 );
        }
        
        // Get and decode configuration JSON
        $config_raw = isset( $_POST['config'] ) ? wp_unslash( $_POST['config'] ) : '{}';
        $config     = json_decode( $config_raw, true );
        
        // Validate JSON decode
        if ( ! is_array( $config ) ) {
            wp_send_json_error( __( 'Invalid data.', 'language-switcher-for-elementor-polylang' ), 400 );
        }
        
        // Sanitize and validate configuration
        $sanitized = $this->sanitize_config( $config );
        
        // Save to database
        update_option( 'lsep_floating_switcher_config', $sanitized );
        
        // Return success response
        wp_send_json_success( __( 'Settings saved successfully.', 'language-switcher-for-elementor-polylang' ) );
    }
    
   /**
     * Sanitize Configuration
     *
     * Sanitizes and validates all configuration fields to ensure
     * data integrity and security before saving to database.
     *
     * @since 1.2.4
     * @param array $config Raw configuration array from client
     * @return array Sanitized and validated configuration array
     */
private function sanitize_config( $config ) {
    $sanitized = [];
    
    // Sanitize boolean fields (convert to true/false)
    $sanitized['enabled'] = ! empty( $config['enabled'] );
    $sanitized['enableCustomCss'] = ! empty( $config['enableCustomCss'] );
    $sanitized['enableTransitions'] = ! empty( $config['enableTransitions'] );
    
    // Validate and sanitize type field (dropdown, inline, or side-by-side)
    $sanitized['type'] = in_array( $config['type'] ?? '', [ 'dropdown', 'inline', 'side-by-side' ], true ) 
    ? $config['type'] 
    : 'dropdown';
    
    // Sanitize color fields (supports hex with alpha or rgba)
    $color_fields = [ 'bgColor', 'bgHoverColor', 'textColor', 'textHoverColor', 'borderColor' ];
    foreach ( $color_fields as $field ) {
        $sanitized[ $field ] = $this->sanitize_color( $config[ $field ] ?? '#ffffff' );
    }
    
    // Sanitize numeric fields (ensure positive integers)
    $sanitized['borderWidth'] = absint( $config['borderWidth'] ?? 1 );
    $sanitized['flagRadius'] = absint( $config['flagRadius'] ?? 2 );
    
    // Sanitize border radius array (4 values for each corner: TL, TR, BR, BL)
    if ( isset( $config['borderRadius'] ) && is_array( $config['borderRadius'] ) ) {
        $sanitized['borderRadius'] = array_map( 'absint', array_slice( $config['borderRadius'], 0, 4 ) );
        // Ensure we have exactly 4 values
        $sanitized['borderRadius'] = array_pad( $sanitized['borderRadius'], 4, 0 );
    } else {
        $sanitized['borderRadius'] = [ 8, 8, 0, 0 ];
    }
    
    // Validate size field (normal or large)
    $sanitized['size'] = in_array( $config['size'] ?? '', [ 'normal', 'large' ], true )
        ? $config['size']
        : 'normal';
    
    // Validate flag shape (rect, square, or rounded)
    $sanitized['flagShape'] = in_array( $config['flagShape'] ?? '', [ 'rect', 'square', 'rounded' ], true )
        ? $config['flagShape']
        : 'rect';
    
    // Sanitize custom CSS (remove dangerous code while preserving CSS)
    $sanitized['customCss'] = '';
if ( ! empty( $config['customCss'] ) ) {
    // Remove script tags and dangerous patterns for security
    $custom_css = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $config['customCss'] );
    $custom_css = preg_replace( '/<style\b[^>]*>.*?<\/style>/is', '', $custom_css );
    $custom_css = wp_kses( $custom_css, array() ); // Strip all HTML tags
    $sanitized['customCss'] = sanitize_textarea_field( $custom_css );
}
    
    // Sanitize layout customizer (device-specific settings)
    $sanitized['layoutCustomizer'] = [];
    
    // Process layout settings for each device (desktop and mobile)
    foreach ( [ 'desktop', 'mobile' ] as $device ) {
        if ( isset( $config['layoutCustomizer'][ $device ] ) && is_array( $config['layoutCustomizer'][ $device ] ) ) {
            $layout = $config['layoutCustomizer'][ $device ];
            
            // Validate position (e.g., 'bottom-right', 'top-left')
            $valid_positions = [
                'bottom-right', 'bottom-left', 'bottom-center',
                'top-right', 'top-left', 'top-center'
            ];
            $sanitized['layoutCustomizer'][ $device ]['position'] = in_array( $layout['position'] ?? '', $valid_positions, true )
                ? $layout['position']
                : 'bottom-right';
            
            // Validate width mode (default or custom)
            $sanitized['layoutCustomizer'][ $device ]['width'] = in_array( $layout['width'] ?? '', [ 'default', 'custom' ], true )
                ? $layout['width']
                : 'default';
            $sanitized['layoutCustomizer'][ $device ]['customWidth'] = absint( $layout['customWidth'] ?? 216 );
            
            // Validate padding mode (default or custom)
            $sanitized['layoutCustomizer'][ $device ]['padding'] = in_array( $layout['padding'] ?? '', [ 'default', 'custom' ], true )
                ? $layout['padding']
                : 'default';
            $sanitized['layoutCustomizer'][ $device ]['customPadding'] = absint( $layout['customPadding'] ?? 0 );
            
            // Validate flag icon position (before, after, or hide)
            $sanitized['layoutCustomizer'][ $device ]['flagIconPosition'] = in_array( $layout['flagIconPosition'] ?? '', [ 'before', 'after', 'hide' ], true )
            ? $layout['flagIconPosition']
            : 'before';
            
            // Validate language names display mode (full, short, or none)
            $sanitized['layoutCustomizer'][ $device ]['languageNames'] = in_array( $layout['languageNames'] ?? '', [ 'full', 'short', 'none' ], true )
                ? $layout['languageNames']
                : 'full';
        } else {
            // If device config missing or invalid, use defaults
            $sanitized['layoutCustomizer'][ $device ] = $this->get_default_config()['layoutCustomizer'][ $device ];
        }
    }
    
    return $sanitized;
}

/**
 * Sanitize Color Value
 *
 * Validates and sanitizes color values.
 * Supports hex colors (with or without alpha) and rgba() format.
 *
 * @since 1.2.4
 * @param string $color Color value to sanitize
 * @return string Sanitized color value or default fallback
 */
private function sanitize_color( $color ) {
    // Remove whitespace from color value
    $color = trim( $color );
    
    // Validate hex colors (#RGB, #RRGGBB, or #RRGGBBAA with alpha)
    if ( preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color ) ) {
        return $color;
    }
    
    // Validate rgba() or rgb() color format
    if ( preg_match( '/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/', $color ) ) {
        return $color;
    }
    
    // Return default white if validation fails
    return '#ffffff';
}
}