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
        add_action('admin_enqueue_scripts', array($this, 'lsep_enqueue_dashboard_scripts'));
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
     * Enqueue dashboard scripts
     */
    public function lsep_enqueue_dashboard_scripts() {
        wp_enqueue_style( 'lsep-dashboard-style', plugin_dir_url( __FILE__ ) . '/css/admin-dashboard.css', null, LSEP_VERSION );
    }

    /**
     * Get Started page content
     */
    public function lsep_get_started_page_content() {
        ?>
            <div class="wrap lsdp-get-started">
                <h1><?php echo esc_html__('Welcome to Language Switcher for Elementor & Polylang', 'language-switcher-for-elementor-polylang'); ?></h1>
                
                <h2 class="nav-tab-wrapper">
                    <a href="#getting-started" class="nav-tab nav-tab-active"><?php echo esc_html__('Getting Started', 'language-switcher-for-elementor-polylang'); ?></a>
                </h2>

                <div class="lsdp-tab-content">
                    <div id="getting-started" class="lsdp-tab-pane active">
                        <div class="lsdp-get-started-content">
                            <h3><?php echo esc_html__('Quick Start Guide', 'language-switcher-for-elementor-polylang'); ?></h3>
                            <p><?php echo esc_html__('Thank you for installing Language Switcher for Elementor & Polylang. This plugin allows you to create a seamless multilingual experience on your Elementor-powered site.', 'language-switcher-for-elementor-polylang'); ?></p>

                            <h4><?php echo esc_html__('1. Add Language Switcher Widget', 'language-switcher-for-elementor-polylang'); ?></h4>
                            <ul>
                                <li><?php echo esc_html__('Open a page using Elementor.', 'language-switcher-for-elementor-polylang'); ?></li>
                                <li><?php echo esc_html__('Search for "Language Switcher" in the Elementor widget panel.', 'language-switcher-for-elementor-polylang'); ?></li>
                                <li><?php echo esc_html__('Drag and drop the widget where you want to show the switcher.', 'language-switcher-for-elementor-polylang'); ?></li>
                                <li><?php echo esc_html__('Customize style, layout, and language display from widget settings.', 'language-switcher-for-elementor-polylang'); ?></li>
                            </ul>

                            <h4><?php echo esc_html__('2. Translate Elementor Templates', 'language-switcher-for-elementor-polylang'); ?></h4>
                            <ul>
                                <li><?php echo esc_html__('Go to Templates > Saved Templates in your WordPress dashboard.', 'language-switcher-for-elementor-polylang'); ?></li>
                                <li><?php echo esc_html__('Create or edit a template and assign it to a specific language via Polylang.', 'language-switcher-for-elementor-polylang'); ?></li>
                                <li><?php echo esc_html__('Design each language version using Elementor.', 'language-switcher-for-elementor-polylang'); ?></li>
                            </ul>

                            <h4><?php echo esc_html__('3. Translations Control Panel', 'language-switcher-for-elementor-polylang'); ?></h4>
                            <p><?php echo esc_html__('Manage and edit translated versions of your pages easily using the Translations Control Panel. This tool shows a list of all configured languages and provides quick access to create or edit the page for each language.', 'language-switcher-for-elementor-polylang'); ?></p>
                            <ul>
                                <li><?php echo esc_html__('See a complete list of available languages configured via Polylang.', 'language-switcher-for-elementor-polylang'); ?></li>
                                <li><?php echo esc_html__('For each page, check the translation status—whether it’s created or still missing.', 'language-switcher-for-elementor-polylang'); ?></li>
                                <li><?php echo esc_html__('Click the "Edit" icon to modify an existing translation.', 'language-switcher-for-elementor-polylang'); ?></li>
                                <li><?php echo esc_html__('Click the "Create" icon to quickly start designing a new translation in Elementor.', 'language-switcher-for-elementor-polylang'); ?></li>
                            </ul>
                            <p><?php echo esc_html__('This feature helps you maintain consistency across multilingual content and improves translation workflow directly from your WordPress dashboard.', 'language-switcher-for-elementor-polylang'); ?></p>
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