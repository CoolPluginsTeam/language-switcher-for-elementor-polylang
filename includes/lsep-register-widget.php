<?php
/**
 * Language Switcher Polylang Elementor Widget
 *
 * @package           LanguageSwitcherPolylangElementor
 * @author            Your Name
 * @copyright         2024 Your Company
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSEP_Register_Widget
 *
 * Handles the registration of custom Elementor widget.
 */
class LSEP_Register_Widget {

	/**
	 * Constructor
	 *
	 * Initialize the class and set up hooks.
	 */
	public function __construct() {
		// Check if Elementor is active
		global $polylang;
        if ( ! isset( $polylang ) ) {
            return;
        }

        if(! is_plugin_active( 'elementor/elementor.php' )){
            return;
        }
		add_action( 'elementor/widgets/register', array( $this, 'lsep_register_widgets' ) );
	}

	/**
	 * Register custom Elementor widgets
	 *
	 * @return void
	 */
	public function lsep_register_widgets() {
		require_once LSEP_PLUGIN_DIR . 'includes/widget/class-lsep-widget.php';
		\Elementor\Plugin::instance()->widgets_manager->register( new \LanguageSwitcherPolylangElementorWidget\LSEP\LSEP_Widget() );
	}
}

// Initialize the widget registration
new LSEP_Register_Widget();