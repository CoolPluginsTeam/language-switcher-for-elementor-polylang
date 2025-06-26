<?php
/**
 * Plugin Name: Language Switcher for Elementor & Polylang
 * Plugin URI:
 * Description: Language Switcher for Elementor & Polylang to use language switcher in your page or Elementor header menu
 * Version:     1.2.1
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author:      satindersingh
 * Author URI:  https://profiles.wordpress.org/satindersingh/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: language-switcher-for-elementor-polylang
 * Requires Plugins: elementor, polylang
 *
 * @package LanguageSwitcherPolylangElementor
 */

namespace LSEP\LanguageSwitcherPolylangElementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Define plugin constants
 */
define( 'LSEP_VERSION', '1.2.1' );
define( 'LSEP_PLUGIN_NAME', 'language-switcher-for-elementor-polylang' );
define( 'LSEP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LSEP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! class_exists( 'LSEP_LanguageSwitcher' ) ) {
    /**
     * Main plugin class
     *
     * @since 1.0.0
     */
    class LSEP_LanguageSwitcher {
        /**
         * Instance of this class
         *
         * @var object
         */
        public static $instance;

        /**
         * Constructor  
         */
        public function __construct() {
            register_activation_hook( __FILE__, array( $this, 'lsep_activate' ) );
            add_action( 'plugins_loaded', array( $this, 'lsep_init' ) );
            add_action( 'admin_init', array( $this, 'lsep_redirect_to_settings' ) );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'lsep_settings_page' ) );
        }

        /**
         * Set settings on plugin activation.
         *
         * @since 1.0.0
         */
        public function lsep_activate() {
			update_option( 'lsep-v', LSEP_VERSION );
			update_option( 'lsep-type', 'FREE' );
            update_option( 'lsep_plugin_activation_redirect', true );
			if (!get_option( 'lsep_initial_save_version' ) ) {
                add_option( 'lsep_initial_save_version', LSEP_VERSION );
            }
            if(!get_option( 'lsep_install_date' ) ) {
                add_option( 'lsep_install_date', gmdate('Y-m-d h:i:s') );
            }
		}

        /**
         * Redirect to settings page on plugin activation.
         *
         * @since 1.0.0
         */
        public function lsep_redirect_to_settings() {
            global $polylang;
            if ( ! isset( $polylang ) ) {
                return;
            }

            if(! is_plugin_active( 'elementor/elementor.php' )){
                return;
            }
            if ( get_option( 'lsep_plugin_activation_redirect', false ) ) {
                delete_option( 'lsep_plugin_activation_redirect' );
                wp_redirect( admin_url( 'admin.php?page=lsep-get-started' ) );
                exit;
            }
        }

        /**
         * Description  Add links in plugin list page
         *
         * @param array $links  The Links you want to add.
         */
        public function lsep_settings_page( $links ) {
            $links[] = '<a style="font-weight:bold" href="' . esc_url( admin_url( 'admin.php?page=lsep-get-started' ) ) . '">' . __( 'Get Started', 'language-switcher-for-elementor-polylang' ) . '</a>';
            return $links;
        }

        /**
         * Initialize plugin
         *
         * @since 1.0.0
         */
        public function lsep_init() {
            require_once LSEP_PLUGIN_DIR . 'includes/lsep-manager.php';
            require_once LSEP_PLUGIN_DIR . 'includes/lsep-register-widget.php';
            
            if ( is_admin() && !defined( LSEP_VERSION ) ) {
                /** Feedback form after deactivation */
                require_once __DIR__ . '/admin/feedback/admin-feedback-form.php';
                require_once LSEP_PLUGIN_DIR . 'admin/dashboard/lsep-dashboard.php';
                cool_plugins_polylang_addon_settings_page( 'polylang-addons', 'cool-plugins-polylang-addons', 'Polylang Addons' );
            }
        }

        /**
         * Get instance of this class
         *
         * @since 1.0.0
         * @return object
         */
        public static function get_instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }

    // Initialize the plugin
    LSEP_LanguageSwitcher::get_instance();
}
