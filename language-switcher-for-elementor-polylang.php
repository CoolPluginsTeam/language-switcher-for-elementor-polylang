<?php
/**
 * Plugin Name: Language Switcher for Elementor & Polylang
 * Plugin URI:
 * Description: Language Switcher for Elementor & Polylang to use language switcher in your page or Elementor header menu
 * Version:     1.0.0
 * Author:      Coolplugins
 * Author URI:  http://coolplugins.net/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: language-switcher-for-elementor-polylang
 * Domain Path: /languages
 *
 * @package LanguageSwitcherPolylangElementor
 */

namespace LanguageSwitcherPolylangElementor\LSEP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Define plugin constants
 */
define( 'LSEP_VERSION', '1.0.0' );
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
            add_action( 'init', array( $this, 'lsep_load_textdomain' ) );
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
			update_option( 'lsep-installDate', gmdate( 'Y-m-d h:i:s' ) );
			update_option( 'lsep-ratingDiv', 'no' );
            update_option( 'lsep_plugin_activation_redirect', true );
			if (!get_option( 'lsep_initial_save_version' ) ) {
                add_option( 'lsep_initial_save_version', LSEP_VERSION );
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
         * Load plugin text domain
         *
         * @since 1.0.0
         */
        public function lsep_load_textdomain() {
            load_plugin_textdomain( 'language-switcher-for-elementor-polylang', false, basename( dirname( __FILE__ ) ) . '/languages' );
        }

        /**
         * Initialize plugin
         *
         * @since 1.0.0
         */
        public function lsep_init() {
            global $polylang;
            if ( ! isset( $polylang ) ) {
                add_action( 'admin_notices', array( $this, 'lsep_required_plugins_admin_notice' ) );
            }
            if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
                add_action( 'admin_notices', array( $this, 'lsep_elementor_required_admin_notice' ) );
            }
            require_once LSEP_PLUGIN_DIR . 'includes/class-lsep-manager.php';
            require_once LSEP_PLUGIN_DIR . 'includes/lsep-register-widget.php';
            require_once LSEP_PLUGIN_DIR . 'admin/dashboard/lsep-dashboard.php';

            if ( is_admin() && !defined( LSEP_VERSION ) ) {
                /** Feedback form after deactivation */
                require_once __DIR__ . '/admin/feedback/admin-feedback-form.php';
                /*** Plugin review notice file */
                require_once __DIR__ . '/admin/lsep-feedback-notice.php';
            }
        }

        /**
         * Admin notice for required Polylang plugin
         *
         * @since 1.0.0
         */
        public function lsep_required_plugins_admin_notice() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $url = esc_url_raw( 'plugin-install.php?tab=plugin-information&plugin=polylang&TB_iframe=true' );
            $title = sanitize_text_field( 'Polylang' );
            $plugin_info = get_plugin_data( __FILE__, true, true );
            $allowed_html = array(
                'a' => array(
                    'href'  => array(),
                    'title' => array(),
                    'class' => array(),
                ),
                'strong' => array(),
            );

            printf(
                '<div class="error"><p>%s</p></div>',
                sprintf(
                    /* translators: 1: Plugin name, 2: Plugin name */
                    esc_html__( 'In order to use %1$s plugin, please install and activate the latest version of %2$s', 'language-switcher-for-elementor-polylang' ),
                    wp_kses( '<strong>' . esc_html( $plugin_info['Name'] ) . '</strong>', $allowed_html ),
                    wp_kses( '<a href="' . esc_url( $url ) . '" class="thickbox" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</a>', $allowed_html )
                )
            );
        }

        /**
         * Admin notice for required Elementor plugin
         *
         * @since 1.0.0
         */
        public function lsep_elementor_required_admin_notice() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $url = esc_url_raw( 'plugin-install.php?tab=plugin-information&plugin=elementor&TB_iframe=true' );
            $title = sanitize_text_field( 'Elementor' );
            $plugin_info = get_plugin_data( __FILE__, true, true );
            $allowed_html = array(
                'a' => array(
                    'href'  => array(),
                    'title' => array(),
                    'class' => array(),
                ),
                'strong' => array(),
            );

            printf(
                '<div class="error"><p>%s</p></div>',
                sprintf(
                    /* translators: 1: Plugin name, 2: Plugin name */
                    esc_html__( 'In order to use %1$s plugin, please install and activate the latest version of %2$s', 'language-switcher-for-elementor-polylang' ),
                    wp_kses( '<strong>' . esc_html( $plugin_info['Name'] ) . '</strong>', $allowed_html ),
                    wp_kses( '<a href="' . esc_url( $url ) . '" class="thickbox" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</a>', $allowed_html )
                )
            );
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
