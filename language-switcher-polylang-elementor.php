<?php
/**
 * Plugin Name: Language Switcher & Translation – Polylang for Elementor
 * Plugin URI:
 * Description: Language Switcher & Translation – Polylang for Elementor to use language switcher in your page or Elementor header menu
 * Version:     1.0.0
 * Author:      Coolplugins
 * Author URI:  http://coolplugins.net/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: language-switcher-polylang-elementor
 * Domain Path: /languages
 *
 * @package LanguageSwitcherPolylangElementor
 */

namespace LanguageSwitcherPolylangElementor\LSP;

/**
 * Define plugin constants
 */
define( 'LSP_VERSION', '1.0.0' );
define( 'LSP_PLUGIN_NAME', 'language-switcher-polylang-elementor' );
define( 'LSP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! class_exists( 'LSP_LanguageSwitcher' ) ) {
    /**
     * Main plugin class
     *
     * @since 1.0.0
     */
    class LSP_LanguageSwitcher {
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
            add_action( 'plugins_loaded', array( $this, 'lsp_init' ) );
            add_action( 'init', array( $this, 'lsp_load_textdomain' ) );
        }

        /**
         * Load plugin text domain
         *
         * @since 1.0.0
         */
        public function lsp_load_textdomain() {
            load_plugin_textdomain( 'language-switcher-polylang-elementor', false, basename( dirname( __FILE__ ) ) . '/languages' );
        }

        /**
         * Initialize plugin
         *
         * @since 1.0.0
         */
        public function lsp_init() {
            global $polylang;
            if ( ! isset( $polylang ) ) {
                add_action( 'admin_notices', array( $this, 'lsp_required_plugins_admin_notice' ) );
            }
            if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
                add_action( 'admin_notices', array( $this, 'lsp_elementor_required_admin_notice' ) );
            }
            require_once LSP_PLUGIN_DIR . 'includes/class-lsp-manager.php';
            require_once LSP_PLUGIN_DIR . 'includes/lsp-register-widget.php';
        }

        /**
         * Admin notice for required Polylang plugin
         *
         * @since 1.0.0
         */
        public function lsp_required_plugins_admin_notice() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $url = 'plugin-install.php?tab=plugin-information&plugin=polylang&TB_iframe=true';
            $title = 'Polylang';
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
                    esc_html__( 'In order to use %1$s plugin, please install and activate the latest version of %2$s', 'language-switcher-polylang-elementor' ),
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
        public function lsp_elementor_required_admin_notice() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $url = 'plugin-install.php?tab=plugin-information&plugin=elementor&TB_iframe=true';
            $title = 'Elementor';
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
                    esc_html__( 'In order to use %1$s plugin, please install and activate the latest version of %2$s', 'language-switcher-polylang-elementor' ),
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
    LSP_LanguageSwitcher::get_instance();
}
