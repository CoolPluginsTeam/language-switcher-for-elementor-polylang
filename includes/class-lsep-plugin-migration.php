<?php
/**
 * Admin notice to migrate to Language Switcher for Polylang.
 *
 * Shows an install/activate button. On success, deactivates this plugin.
 * Blocks reactivation while the successor plugin is present.
 *
 * @package Language_Switcher_Polylang_Elementor
 * @since   1.2.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSEP_Plugin_Migration
 *
 * @since 1.2.6
 */
class LSEP_Plugin_Migration {

	/**
	 * WordPress.org slug of the successor plugin.
	 *
	 * @since 1.2.6
	 * @var string
	 */
	const SUCCESSOR_SLUG = 'language-switcher-for-divi-polylang';

	/**
	 * Plugin basename of the successor plugin.
	 *
	 * @since 1.2.6
	 * @var string
	 */
	const SUCCESSOR_BASENAME = 'language-switcher-for-divi-polylang/language-switcher-for-divi-polylang.php';

	/**
	 * AJAX action name for install/activate.
	 *
	 * @since 1.2.6
	 * @var string
	 */
	const AJAX_ACTION = 'lsep_install_activate_successor';

	/**
	 * AJAX action for dismissing the notice.
	 *
	 * @since 1.2.6
	 * @var string
	 */
	const DISMISS_ACTION = 'lsep_dismiss_migration_notice';

	/**
	 * User meta key for dismissed notice.
	 *
	 * @since 1.2.6
	 * @var string
	 */
	const DISMISS_META = 'lsep_migration_notice_dismissed';

	/**
	 * Allowed download hosts for the successor plugin package.
	 *
	 * @since 1.2.6
	 * @var string[]
	 */
	const ALLOWED_DOWNLOAD_HOSTS = array(
		'downloads.wordpress.org',
	);

	/**
	 * Check whether the successor plugin files are installed.
	 *
	 * @since 1.2.6
	 *
	 * @return bool
	 */
	public static function is_successor_present() {
		return file_exists( WP_PLUGIN_DIR . '/' . self::SUCCESSOR_BASENAME );
	}

	/**
	 * Ensure plugin.php helpers are loaded.
	 *
	 * @since 1.2.6
	 *
	 * @return void
	 */
	private static function load_plugin_helpers() {
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	}

	/**
	 * Block activation when the successor plugin is already installed.
	 *
	 * @since 1.2.6
	 *
	 * @param string $plugin_file Absolute path to this plugin's main file.
	 * @return void
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
			'<strong>' . esc_html__( 'Language Switcher for Polylang', 'language-switcher-for-elementor-polylang' ) . '</strong>'
		);

		wp_die(
			wp_kses(
				$message,
				array(
					'strong' => array(),
				)
			),
			esc_html__( 'Plugin activation blocked', 'language-switcher-for-elementor-polylang' ),
			array(
				'back_link' => true,
			)
		);
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.2.6
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_deactivate_if_successor_present' ), 1 );
		add_action( 'admin_notices', array( __CLASS__, 'render_migration_notice' ) );
		add_action( 'after_plugin_row_' . plugin_basename( LSEP_PLUGIN_FILE ), array( __CLASS__, 'render_plugin_row_notice' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_notice_assets' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_install_activate_successor' ) );
		add_action( 'wp_ajax_' . self::DISMISS_ACTION, array( __CLASS__, 'ajax_dismiss_notice' ) );
	}

	/**
	 * Deactivate this plugin when the successor is active.
	 *
	 * @since 1.2.6
	 *
	 * @return void
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
	 * @since 1.2.6
	 *
	 * @return bool
	 */
	private static function can_manage_migration() {
		if ( ! is_user_logged_in() || ! current_user_can( 'activate_plugins' ) ) {
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
	 * @since 1.2.6
	 *
	 * @return bool
	 */
	private static function is_successor_active() {
		self::load_plugin_helpers();

		return self::is_successor_present() && is_plugin_active( self::SUCCESSOR_BASENAME );
	}

	/**
	 * Whether migration messaging should be shown at all.
	 *
	 * @since 1.2.6
	 *
	 * @return bool
	 */
	private static function should_show_migration() {
		return self::can_manage_migration() && ! self::is_successor_active();
	}

	/**
	 * Migration notice message text.
	 *
	 * @since 1.2.6
	 *
	 * @return string
	 */
	private static function get_migration_message() {
		return __( 'Language Switcher for Elementor & Polylang is deprecated. All features are now included in Language Switcher for Polylang. Please use the new plugin for future updates and support.', 'language-switcher-for-elementor-polylang' );
	}

	/**
	 * Allowed HTML for the plugin-row notice markup.
	 *
	 * @since 1.2.6
	 *
	 * @return array
	 */
	private static function get_notice_allowed_html() {
		return array(
			'tr'     => array(
				'class' => true,
			),
			'td'     => array(
				'colspan' => true,
				'class'   => true,
			),
			'div'    => array(
				'class' => true,
			),
			'p'      => array(),
			'button' => array(
				'type'       => true,
				'class'      => true,
				'id'         => true,
				'data-label' => true,
			),
		);
	}

	/**
	 * Render the Migrate Now button.
	 *
	 * @since 1.2.6
	 *
	 * @return void
	 */
	private static function render_migrate_button() {
		$label = __( 'Migrate Now', 'language-switcher-for-elementor-polylang' );
		?>
		<button
			type="button"
			class="button button-primary button-small"
			id="lsep-install-successor"
			data-label="<?php echo esc_attr( $label ); ?>"
		>
			<?php echo esc_html( $label ); ?>
		</button>
		<?php
	}

	/**
	 * Enqueue assets for the migration notice.
	 *
	 * @since 1.2.6
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_notice_assets( $hook ) {
		if ( ! self::should_show_migration() ) {
			return;
		}

		// Plugin-row fallback only appears on the plugins screen.
		if ( self::is_notice_dismissed() && 'plugins.php' !== $hook ) {
			return;
		}

		$handle      = 'lsep-migration-notice';
		$plugin_file = plugin_basename( LSEP_PLUGIN_FILE );

		wp_register_script( $handle, false, array( 'jquery' ), LSEP_VERSION, true );
		wp_enqueue_script( $handle );

		wp_register_style( $handle, false, array(), LSEP_VERSION );
		wp_enqueue_style( $handle );
		wp_add_inline_style(
			$handle,
			'.lsep-migration-notice p,.lsep-migration-plugin-row .update-message p{display:flex;align-items:center;flex-wrap:wrap;}.lsep-migration-notice #lsep-install-successor,.lsep-migration-plugin-row #lsep-install-successor{margin-left:10px;vertical-align:middle;}'
		);

		wp_localize_script(
			$handle,
			'lsepMigration',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'action'        => self::AJAX_ACTION,
				'dismissAction' => self::DISMISS_ACTION,
				'nonce'         => wp_create_nonce( self::AJAX_ACTION ),
				'dismissNonce'  => wp_create_nonce( self::DISMISS_ACTION ),
				'isInstalled'   => self::is_successor_present(),
				'isPluginsPage' => ( 'plugins.php' === $hook ),
				'pluginFile'    => $plugin_file,
				'pluginRowHtml' => self::get_plugin_row_notice_html(),
				'installing'    => __( 'Installing…', 'language-switcher-for-elementor-polylang' ),
				'activating'    => __( 'Activating…', 'language-switcher-for-elementor-polylang' ),
				'errorFallback' => __( 'Something went wrong. Please try again or install the plugin manually from WordPress.org.', 'language-switcher-for-elementor-polylang' ),
			)
		);

		wp_add_inline_script( $handle, self::get_notice_script() );
	}

	/**
	 * Inline script used by the migration notice.
	 *
	 * @since 1.2.6
	 *
	 * @return string
	 */
	private static function get_notice_script() {
		return <<<'JS'
(function ($) {
	function showPluginRowNotice() {
		if (!lsepMigration.isPluginsPage || !lsepMigration.pluginRowHtml) {
			return;
		}
		if ($('.lsep-migration-plugin-row').length) {
			return;
		}
		var $pluginRow = $('#the-list').find('tr[data-plugin="' + lsepMigration.pluginFile + '"]');
		if (!$pluginRow.length) {
			return;
		}
		$pluginRow.after(lsepMigration.pluginRowHtml);
		$pluginRow.addClass('update');
	}

	$(document).on('click', '#lsep-install-successor', function (e) {
		e.preventDefault();
		var $btn = $(this);
		if ($btn.prop('disabled')) {
			return;
		}
		$btn.prop('disabled', true).text(lsepMigration.isInstalled ? lsepMigration.activating : lsepMigration.installing);
		$.post(lsepMigration.ajaxUrl, {
			action: lsepMigration.action,
			_wpnonce: lsepMigration.nonce
		}).done(function (res) {
			if (res && res.success) {
				$btn.text(lsepMigration.activating);
				window.location.href = (res.data && res.data.redirect) ? res.data.redirect : window.location.href;
				return;
			}
			var msg = (res && res.data && res.data.message) ? res.data.message : lsepMigration.errorFallback;
			$btn.prop('disabled', false).text($btn.data('label'));
			window.alert(msg);
		}).fail(function () {
			$btn.prop('disabled', false).text($btn.data('label'));
			window.alert(lsepMigration.errorFallback);
		});
	});

	$(document).on('click', '.lsep-migration-notice .notice-dismiss', function () {
		$.post(lsepMigration.ajaxUrl, {
			action: lsepMigration.dismissAction,
			_wpnonce: lsepMigration.dismissNonce
		}).always(function () {
			showPluginRowNotice();
		});
	});
})(jQuery);
JS;
	}

	/**
	 * Whether the current user dismissed the migration notice.
	 *
	 * @since 1.2.6
	 *
	 * @return bool
	 */
	private static function is_notice_dismissed() {
		return (bool) get_user_meta( get_current_user_id(), self::DISMISS_META, true );
	}

	/**
	 * AJAX: persist notice dismissal for the current user.
	 *
	 * @since 1.2.6
	 *
	 * @return void
	 */
	public static function ajax_dismiss_notice() {
		check_ajax_referer( self::DISMISS_ACTION, '_wpnonce' );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to dismiss this notice.', 'language-switcher-for-elementor-polylang' ),
				),
				403
			);
		}

		update_user_meta( get_current_user_id(), self::DISMISS_META, 1 );
		wp_send_json_success();
	}

	/**
	 * Render admin notice with Migrate Now button.
	 *
	 * @since 1.2.6
	 *
	 * @return void
	 */
	public static function render_migration_notice() {
		if ( ! self::should_show_migration() || self::is_notice_dismissed() ) {
			return;
		}
		?>
		<div class="notice notice-warning is-dismissible lsep-migration-notice">
			<p>
				<?php echo esc_html( self::get_migration_message() ); ?>
				<?php self::render_migrate_button(); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Build HTML for the plugins list row notice.
	 *
	 * @since 1.2.6
	 *
	 * @return string
	 */
	private static function get_plugin_row_notice_html() {
		$colspan = 3;
		if ( function_exists( '_get_list_table' ) ) {
			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
			if ( $wp_list_table && method_exists( $wp_list_table, 'get_column_count' ) ) {
				$colspan = absint( $wp_list_table->get_column_count() );
			}
		}

		ob_start();
		?>
		<tr class="plugin-update-tr active lsep-migration-plugin-row">
			<td colspan="<?php echo esc_attr( (string) $colspan ); ?>" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-warning notice-alt">
					<p>
						<?php echo esc_html( self::get_migration_message() ); ?>
						<?php self::render_migrate_button(); ?>
					</p>
				</div>
			</td>
		</tr>
		<?php
		$html = (string) ob_get_clean();

		return wp_kses( $html, self::get_notice_allowed_html() );
	}

	/**
	 * Render migration message below this plugin row on the plugins screen.
	 *
	 * @since 1.2.6
	 *
	 * @param string $plugin_file Plugin basename.
	 * @param array  $plugin_data Plugin data from the plugins list table.
	 * @return void
	 */
	public static function render_plugin_row_notice( $plugin_file, $plugin_data ) {
		unset( $plugin_data );

		if ( plugin_basename( LSEP_PLUGIN_FILE ) !== $plugin_file ) {
			return;
		}

		if ( ! self::should_show_migration() || ! self::is_notice_dismissed() ) {
			return;
		}

		echo wp_kses( self::get_plugin_row_notice_html(), self::get_notice_allowed_html() );
	}

	/**
	 * AJAX: install (if needed) and activate successor, then deactivate this plugin.
	 *
	 * @since 1.2.6
	 *
	 * @return void
	 */
	public static function ajax_install_activate_successor() {
		check_ajax_referer( self::AJAX_ACTION, '_wpnonce' );

		if ( ! self::can_manage_migration() ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'You do not have permission to install or activate plugins.', 'language-switcher-for-elementor-polylang' ),
				),
				403
			);
		}

		self::load_plugin_helpers();

		if ( ! self::is_successor_present() ) {
			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html__( 'You do not have permission to install plugins.', 'language-switcher-for-elementor-polylang' ),
					),
					403
				);
			}

			$install = self::install_successor();
			if ( is_wp_error( $install ) ) {
				wp_send_json_error(
					array(
						'message' => esc_html( $install->get_error_message() ),
					)
				);
			}
		}

		$activate = activate_plugin( self::SUCCESSOR_BASENAME, '', false, false );
		if ( is_wp_error( $activate ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html( $activate->get_error_message() ),
				)
			);
		}

		self::deactivate_self();

		wp_send_json_success(
			array(
				'message'  => esc_html__( 'Language Switcher for Polylang is now active. This plugin has been deactivated.', 'language-switcher-for-elementor-polylang' ),
				'redirect' => esc_url_raw( admin_url( 'plugins.php' ) ),
			)
		);
	}

	/**
	 * Validate that a download URL is from an allowed WordPress.org host.
	 *
	 * @since 1.2.6
	 *
	 * @param string $download_link Package URL.
	 * @return true|WP_Error
	 */
	private static function validate_download_link( $download_link ) {
		$download_link = esc_url_raw( $download_link );
		if ( empty( $download_link ) ) {
			return new WP_Error(
				'lsep_missing_download',
				__( 'Download link for Language Switcher for Polylang was not found.', 'language-switcher-for-elementor-polylang' )
			);
		}

		$host = wp_parse_url( $download_link, PHP_URL_HOST );
		if ( empty( $host ) || ! in_array( $host, self::ALLOWED_DOWNLOAD_HOSTS, true ) ) {
			return new WP_Error(
				'lsep_invalid_download_host',
				__( 'Invalid download source for Language Switcher for Polylang.', 'language-switcher-for-elementor-polylang' )
			);
		}

		return true;
	}

	/**
	 * Download and install the successor plugin from WordPress.org.
	 *
	 * @since 1.2.6
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
				__( 'Download link for Language Switcher for Polylang was not found.', 'language-switcher-for-elementor-polylang' )
			);
		}

		$validated = self::validate_download_link( $api->download_link );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( esc_url_raw( $api->download_link ) );

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
				__( 'Installation of Language Switcher for Polylang failed.', 'language-switcher-for-elementor-polylang' )
			);
		}

		return true;
	}

	/**
	 * Deactivate this plugin quietly.
	 *
	 * @since 1.2.6
	 *
	 * @return void
	 */
	private static function deactivate_self() {
		self::load_plugin_helpers();

		$basename = plugin_basename( LSEP_PLUGIN_FILE );
		if ( is_plugin_active( $basename ) ) {
			deactivate_plugins( $basename );
		}
	}
}
