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
 * Class LSP_Register_Widget
 *
 * Handles the registration of custom Elementor widget.
 */
class LSP_Register_Widget {

	/**
	 * Constructor
	 *
	 * Initialize the class and set up hooks.
	 */
	public function __construct() {
		add_action( 'elementor/widgets/register', array( $this, 'lsp_register_widgets' ) );
	}

	/**
	 * Register custom Elementor widgets
	 *
	 * @return void
	 */
	public function lsp_register_widgets() {
		require_once LSP_PLUGIN_DIR . 'includes/widget/class-lsp-widget.php';
		\Elementor\Plugin::instance()->widgets_manager->register( new \LanguageSwitcherPolylangElementorWidget\LSP\LSP_Widget() );
	}
}

// Initialize the widget registration
new LSP_Register_Widget();