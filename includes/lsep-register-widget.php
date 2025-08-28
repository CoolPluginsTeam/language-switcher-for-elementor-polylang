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
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'add_editor_js' ) );
		add_action( 'wp_ajax_lsep_elementor_review_notice', array( $this, 'lsep_elementor_review_notice' ) );
	}

	/**
	 * Register custom Elementor widgets
	 *
	 * @return void
	 */
	public function lsep_register_widgets() {
		require_once LSEP_PLUGIN_DIR . 'includes/widget/lsep-widget.php';
		\Elementor\Plugin::instance()->widgets_manager->register( new LSEP\LanguageSwitcherPolylangElementorWidget\LSEP_Widget() );
	}

	public function add_editor_js() {
		wp_enqueue_script( 'lsep-editor-js', LSEP_PLUGIN_URL . 'includes/js/lsep-editor.js', array( 'jquery' ), LSEP_VERSION, true );
	}

	// Elementor Review notice ajax request function
	public function lsep_elementor_review_notice() {
		if ( ! check_ajax_referer( 'lsep_elementor_review', 'nonce', false ) ) {
			wp_send_json_error( __( 'Invalid security token sent.', 'language-switcher-for-elementor-polylang' ) );
			wp_die( '0', 400 );
		}

		if ( isset( $_POST['lsep_notice_dismiss'] ) && 'true' === sanitize_text_field(wp_unslash($_POST['lsep_notice_dismiss'])) ) {
			update_option( 'lsep_elementor_review_notice_dismiss', 'yes' );
		}
		exit;
	}
}

// Initialize the widget registration
new LSEP_Register_Widget();