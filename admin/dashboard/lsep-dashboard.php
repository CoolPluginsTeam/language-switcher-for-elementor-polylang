<?php
/**
 * Get Started Page
 *
 * @package LanguageSwitcherPolylangElementor
 */

namespace LanguageSwitcherPolylangElementor\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class LSEP_Get_Started {
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
        add_action('admin_menu', array($this, 'lsep_add_get_started_page'), 100);
		wp_enqueue_style( 'lsep-dashboard-style', plugin_dir_url( __FILE__ ) . '/css/admin-dashboard.css', null, LSEP_VERSION );
    }

    /**
     * Add Get Started page under Polylang menu
     */
    public function lsep_add_get_started_page() {
        add_submenu_page(
            'mlang',
            __('Get Started', 'language-switcher-for-elementor-polylang'),
            __('Get Started', 'language-switcher-for-elementor-polylang'),
            'manage_options',
            'lsep-get-started',
            array($this, 'lsep_get_started_page_content')
        );
    }

    /**
     * Get Started page content
     */
    public function lsep_get_started_page_content() {
        ?>
        <div class="wrap lsep-get-started">
            <h1><?php echo esc_html__('Welcome to Language Switcher for Elementor & Polylang', 'language-switcher-for-elementor-polylang'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="#getting-started" class="nav-tab nav-tab-active"><?php echo esc_html__('Getting Started', 'language-switcher-for-elementor-polylang'); ?></a>
            </h2>

            <div class="lsep-tab-content">
                <div id="getting-started" class="lsep-tab-pane active">
                    <div class="lsep-get-started-content">
                        <h3><?php echo esc_html__('Quick Start Guide', 'language-switcher-for-elementor-polylang'); ?></h3>
                        <p><?php echo esc_html__('Thank you for installing Language Switcher for Elementor & Polylang. This plugin allows you to add a language switcher to your Elementor pages and menus.', 'language-switcher-for-elementor-polylang'); ?></p>
                        
                        <h4><?php echo esc_html__('How to Use', 'language-switcher-for-elementor-polylang'); ?></h4>
                        <ol>
                            <li><?php echo esc_html__('Make sure Polylang is installed and configured with your languages', 'language-switcher-for-elementor-polylang'); ?></li>
                            <li><?php echo esc_html__('Edit your page with Elementor', 'language-switcher-for-elementor-polylang'); ?></li>
                            <li><?php echo esc_html__('Search for "Language Switcher" in the Elementor modules panel', 'language-switcher-for-elementor-polylang'); ?></li>
                            <li><?php echo esc_html__('Drag and drop the widget where you want to display the language switcher', 'language-switcher-for-divi-polylang'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get instance of this class
     *
     * @return object
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

// Initialize the Get Started page
LSEP_Get_Started::get_instance(); 