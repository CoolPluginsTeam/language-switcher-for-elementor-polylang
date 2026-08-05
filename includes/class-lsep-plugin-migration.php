<?php
/**
 * Admin notice to migrate to Language Switcher for Polylang – Elementor, Gutenberg, & Divi.
 *
 * Shows an install/activate button. On success, deactivates this plugin.
 * Blocks reactivation while the successor plugin is present.
 *
 * @package Language_Switcher_Polylang_Elementor
 * @since 1.2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSEP_Plugin_Migration
 */
class LSEP_Plugin_Migration {

	/**
	 * WordPress.org slug of the successor plugin.
	 *
	 * @var string
	 */
	const SUCCESSOR_SLUG = 'language-switcher-for-divi-polylang';

	/**
	 * Plugin basename of the successor plugin.
	 *
	 * @var string
	 */
	const SUCCESSOR_BASENAME = 'language-switcher-for-divi-polylang/language-switcher-for-divi-polylang.php';

	/**
	 * AJAX action name.
	 *
	 * @var string
	 */
	const AJAX_ACTION = 'lsep_install_activate_successor';

	/**
	 * AJAX action for dismissing the notice.
	 *
	 * @var string
	 */
	const DISMISS_ACTION = 'lsep_dismiss_migration_notice';

	/**
	 * User meta key for dismissed notice.
	 *
	 * @var string
	 */
	const DISMISS_META = 'lsep_migration_notice_dismissed';

	/**
	 * Check whether the successor plugin files are installed.
	 *
	 * @return bool
	 */
	public static function is_successor_present() {
		return file_exists( WP_PLUGIN_DIR . '/' . self::SUCCESSOR_BASENAME );
	}

	/**
	 * Ensure plugin.php helpers are loaded.
	 */
	private static function load_plugin_helpers() {
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	/**
	 * Block activation when the successor plugin is already installed.
	 *
	 * @param string $plugin_file Absolute path to this plugin's main file.
	 */
	public static function block_activation_if_successor_present( $plugin_file ) {
		if ( ! self::is_successor_present() ) {
			return;
		}

		self::load_plugin_helpers();
		deactivate_plugins( plugin_basename( $plugin_file ) );

		$message = sprintf(
			/* translators: %s: successor plugin name */
			__( 'Language Switcher for Elementor & Polylang has been deprecated and replaced by %s. Please use that plugin instead. This plugin cannot be activated while it is installed.', 'language-switcher-for-elementor-polylang' ),
			'<strong>Language Switcher for Polylang – Elementor, Gutenberg, & Divi</strong>'
		);

		wp_die(
			wp_kses(
				$message,
				array(
					'strong' => array(),
				)
			),
			esc_html__( 'Plugin activation blocked', 'language-switcher-for-elementor-polylang' ),
			array( 'back_link' => true )
		);
	}

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_deactivate_if_successor_present' ), 1 );
		add_action( 'admin_notices', array( __CLASS__, 'render_migration_notice' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_notice_assets' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_install_activate_successor' ) );
		add_action( 'wp_ajax_' . self::DISMISS_ACTION, array( __CLASS__, 'ajax_dismiss_notice' ) );
	}

	/**
	 * Deactivate this plugin when the successor is active.
	 */
	public static function maybe_deactivate_if_successor_present() {
		if ( ! current_user_can( 'activate_plugins' ) || ! self::is_successor_active() ) {
			return;
		}

		self::deactivate_self();
	}

	/**
	 * Whether the current user can see/use the migration notice.
	 *
	 * @return bool
	 */
	private static function can_manage_migration() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}

		// Install capability is only required when the successor is not yet installed.
		if ( ! self::is_successor_present() && ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether the successor plugin is installed and active.
	 *
	 * @return bool
	 */
	private static function is_successor_active() {
		self::load_plugin_helpers();
		return self::is_successor_present() && is_plugin_active( self::SUCCESSOR_BASENAME );
	}

	/**
	 * Enqueue inline JS for the notice button.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_notice_assets( $hook ) {
		unset( $hook );

		if ( ! self::can_manage_migration() || self::is_successor_active() || self::is_notice_dismissed() ) {
			return;
		}

		$is_installed = self::is_successor_present();
		$handle       = 'lsep-migration-notice';

		wp_register_script( $handle, false, array( 'jquery' ), LSEP_VERSION, true );
		wp_enqueue_script( $handle );
		wp_localize_script(
			$handle,
			'lsepMigration',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'action'        => self::AJAX_ACTION,
				'dismissAction' => self::DISMISS_ACTION,
				'nonce'         => wp_create_nonce( self::AJAX_ACTION ),
				'dismissNonce'  => wp_create_nonce( self::DISMISS_ACTION ),
				'isInstalled'   => $is_installed,
				'installing'    => __( 'Installing…', 'language-switcher-for-elementor-polylang' ),
				'activating'    => __( 'Activating…', 'language-switcher-for-elementor-polylang' ),
				'errorFallback' => __( 'Something went wrong. Please try again or install the plugin manually from WordPress.org.', 'language-switcher-for-elementor-polylang' ),
			)
		);

		wp_add_inline_script(
			$handle,
			"(function($){
				$(document).on('click', '#lsep-install-successor', function(e){
					e.preventDefault();
					var \$btn = $(this);
					if (\$btn.prop('disabled')) { return; }
					\$btn.prop('disabled', true).text(lsepMigration.isInstalled ? lsepMigration.activating : lsepMigration.installing);
					$.post(lsepMigration.ajaxUrl, {
						action: lsepMigration.action,
						_wpnonce: lsepMigration.nonce
					}).done(function(res){
						if (res && res.success) {
							\$btn.text(lsepMigration.activating);
							window.location.href = (res.data && res.data.redirect) ? res.data.redirect : window.location.href;
							return;
						}
						var msg = (res && res.data && res.data.message) ? res.data.message : lsepMigration.errorFallback;
						\$btn.prop('disabled', false).text(\$btn.data('label'));
						alert(msg);
					}).fail(function(){
						\$btn.prop('disabled', false).text(\$btn.data('label'));
						alert(lsepMigration.errorFallback);
					});
				});
				$(document).on('click', '.lsep-migration-notice .notice-dismiss', function(){
					$.post(lsepMigration.ajaxUrl, {
						action: lsepMigration.dismissAction,
						_wpnonce: lsepMigration.dismissNonce
					});
				});
			})(jQuery);"
		);
	}

	/**
	 * Whether the current user dismissed the migration notice.
	 *
	 * @return bool
	 */
	private static function is_notice_dismissed() {
		return (bool) get_user_meta( get_current_user_id(), self::DISMISS_META, true );
	}

	/**
	 * AJAX: persist notice dismissal for the current user.
	 */
	public static function ajax_dismiss_notice() {
		if ( ! check_ajax_referer( self::DISMISS_ACTION, '_wpnonce', false ) ) {
			wp_send_json_error( null, 403 );
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( null, 403 );
		}

		update_user_meta( get_current_user_id(), self::DISMISS_META, 1 );
		wp_send_json_success();
	}

	/**
	 * Render admin notice with Install & Activate button.
	 */
	public static function render_migration_notice() {
		if ( ! self::can_manage_migration() || self::is_successor_active() || self::is_notice_dismissed() ) {
			return;
		}

		$button_label = self::is_successor_present()
			? __( 'Activate', 'language-switcher-for-elementor-polylang' )
			: __( 'Install & Activate', 'language-switcher-for-elementor-polylang' );
		?>
		<div class="notice notice-warning is-dismissible lsep-migration-notice">
			<p>
				<button
					type="button"
					class="button button-primary"
					id="lsep-install-successor"
					data-label="<?php echo esc_attr( $button_label ); ?>"
				>
					<?php esc_html_e( 'Try It Now !', 'language-switcher-for-elementor-polylang' ); ?>
				</button>
				<?php esc_html_e( 'Language Switcher for Elementor & Polylang has been deprecated. Please use Language Switcher for Polylang – Elementor, Gutenberg, & Divi which now includes all its features and future updates.', 'language-switcher-for-elementor-polylang' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX: install (if needed) and activate successor, then deactivate this plugin.
	 */
	public static function ajax_install_activate_successor() {
		if ( ! check_ajax_referer( self::AJAX_ACTION, '_wpnonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid security token.', 'language-switcher-for-elementor-polylang' ),
				),
				403
			);
		}

		if ( ! self::can_manage_migration() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to install or activate plugins.', 'language-switcher-for-elementor-polylang' ),
				),
				403
			);
		}

		self::load_plugin_helpers();

		if ( ! self::is_successor_present() ) {
			$install = self::install_successor();
			if ( is_wp_error( $install ) ) {
				wp_send_json_error(
					array(
						'message' => $install->get_error_message(),
					)
				);
			}
		}

		$activate = activate_plugin( self::SUCCESSOR_BASENAME, '', false, false );
		if ( is_wp_error( $activate ) ) {
			wp_send_json_error(
				array(
					'message' => $activate->get_error_message(),
				)
			);
		}

		self::deactivate_self();

		wp_send_json_success(
			array(
				'message'  => __( 'Language Switcher for Polylang – Elementor, Gutenberg, & Divi is now active. This plugin has been deactivated.', 'language-switcher-for-elementor-polylang' ),
				'redirect' => admin_url( 'plugins.php' ),
			)
		);
	}

	/**
	 * Download and install the successor plugin from WordPress.org.
	 *
	 * @return true|WP_Error
	 */
	private static function install_successor() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => self::SUCCESSOR_SLUG,
				'fields' => array(
					'sections' => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			return $api;
		}

		if ( empty( $api->download_link ) ) {
			return new WP_Error(
				'lsep_missing_download',
				__( 'Download link for Language Switcher for Polylang – Elementor, Gutenberg, & Divi was not found.', 'language-switcher-for-elementor-polylang' )
			);
		}

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$skin_errors = $skin->get_errors();
		if ( is_wp_error( $skin_errors ) && $skin_errors->has_errors() ) {
			return $skin_errors;
		}

		if ( null === $result || false === $result || ! self::is_successor_present() ) {
			return new WP_Error(
				'lsep_install_failed',
				__( 'Installation of Language Switcher for Polylang – Elementor, Gutenberg, & Divi failed.', 'language-switcher-for-elementor-polylang' )
			);
		}

		return true;
	}

	/**
	 * Deactivate this plugin quietly.
	 */
	private static function deactivate_self() {
		self::load_plugin_helpers();

		$basename = plugin_basename( LSEP_PLUGIN_FILE );
		if ( is_plugin_active( $basename ) ) {
			deactivate_plugins( $basename );
		}
	}
}
