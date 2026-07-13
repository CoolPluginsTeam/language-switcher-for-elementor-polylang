<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin dashboard for Language Switcher settings.
 */
class cool_plugins_lsep_polylang_addons {

	/**
	 * Singleton instance.
	 *
	 * @var cool_plugins_lsep_polylang_addons|null
	 */
	private static $instance = null;

	/**
	 * Dashboard includes directory.
	 *
	 * @var string
	 */
	private $addon_dir = __DIR__;

	/**
	 * Get singleton instance.
	 *
	 * @return cool_plugins_lsep_polylang_addons
	 */
	public static function init() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register dashboard hooks.
	 */
	public function register_dashboard() {
		add_action( 'admin_menu', array( $this, 'init_plugins_dasboard_page' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_required_scripts' ) );
		add_action( 'in_admin_header', array( $this, 'suppress_foreign_admin_notices' ), 1000 );
	}

	/**
	 * Whether the current request is rendering this plugin dashboard.
	 *
	 * @return bool
	 */
	private function is_dashboard_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for page detection.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		return 'lsep-get-started' === $page;
	}

	/**
	 * Remove third-party plugin and theme admin notices on this dashboard.
	 */
	public function suppress_foreign_admin_notices() {
		if ( ! $this->is_dashboard_page() ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
		remove_all_actions( 'user_admin_notices' );
	}

	/**
	 * Register dashboard submenu page.
	 */
	public function init_plugins_dasboard_page() {
		add_submenu_page(
			'mlang',
			__( 'Language Switcher', 'language-switcher-for-elementor-polylang' ),
			__( 'Language Switcher', 'language-switcher-for-elementor-polylang' ),
			'manage_options',
			'lsep-get-started',
			array( $this, 'displayPluginAdminDashboard' )
		);
	}

	/**
	 * Render dashboard page.
	 */
	public function displayPluginAdminDashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for tab display.
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'getting-started';
		$page_url    = admin_url( 'admin.php?page=lsep-get-started' );
		$logo_url    = plugin_dir_url( __FILE__ ) . 'assets/images/language-switcher-for-elementor-polylang.png';

		echo '<div class="wrap lsep-dashboard-wrap">';

		echo '<div class="lsep-dashboard-header">';
		echo '<div class="lsep-header-content">';
		echo '<div class="lsep-header-logo">';
		echo '<img src="' . esc_url( $logo_url ) . '" alt="" />';
		echo '<h1 class="lsep-header-title">' . esc_html__( 'Language Switcher for Polylang', 'language-switcher-for-elementor-polylang' ) . '</h1>';
		echo '</div>';
		echo '<div class="lsep-header-actions">';
		echo '<a href="' . esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/#new-topic-0' ) . '" class="button button-secondary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get Support', 'language-switcher-for-elementor-polylang' ) . '</a>';
		echo '<a href="' . esc_url( 'https://docs.coolplugins.net/doc/language-switcher-for-elementor-polylang/?utm_source=lsep_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_header' ) . '" class="button button-secondary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'language-switcher-for-elementor-polylang' ) . '</a>';
		echo '</div>';
		echo '</div>';
		echo '</div>';

		echo '<h2 class="nav-tab-wrapper">';
		echo '<a href="' . esc_url( add_query_arg( 'tab', 'getting-started', $page_url ) ) . '" class="nav-tab' . ( 'getting-started' === $current_tab ? ' nav-tab-active' : '' ) . '">' . esc_html__( 'Get Started', 'language-switcher-for-elementor-polylang' ) . '</a>';
		echo '<a href="' . esc_url( add_query_arg( 'tab', 'floating-switcher', $page_url ) ) . '" class="nav-tab' . ( 'floating-switcher' === $current_tab ? ' nav-tab-active' : '' ) . '">' . esc_html__( 'Floating Language Switcher', 'language-switcher-for-elementor-polylang' ) . '</a>';
		echo '</h2>';

		echo '<div class="lsep-tab-content-wrapper">';
		if ( 'floating-switcher' === $current_tab ) {
			$this->floating_switcher_content();
		} else {
			$this->get_started_content();
		}
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render floating switcher tab content.
	 */
	public function floating_switcher_content() {
		$lsep_languages = function_exists( 'pll_languages_list' ) ? pll_languages_list() : array();

		if ( empty( $lsep_languages ) ) {
			echo '<div class="notice notice-warning"><p>';
			echo '<strong>' . esc_html__( 'No languages configured!', 'language-switcher-for-elementor-polylang' ) . '</strong><br>';
			echo esc_html__( 'Please configure at least two languages in Polylang settings.', 'language-switcher-for-elementor-polylang' );
			echo '</p></div>';
		}

		echo '<div id="lsep-floater-app-root"></div>';
	}

	/**
	 * Render get started tab content.
	 */
	public function get_started_content() {
		require $this->addon_dir . '/includes/get-started-content.php';
	}

	/**
	 * Enqueue dashboard styles.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_required_scripts( $hook ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for conditional asset loading.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( 'lsep-get-started' !== $page && false === strpos( $hook, 'lsep-get-started' ) ) {
			return;
		}

		wp_enqueue_style(
			'cool-lsep-plugins-polylang-addon',
			plugin_dir_url( __FILE__ ) . 'assets/css/styles.css',
			array(),
			LSEP_VERSION,
			'all'
		);
	}
}

/**
 * Initialize the dashboard.
 */
function cool_plugins_lsep_polylang_addon_settings_page() {
	$page = cool_plugins_lsep_polylang_addons::init();
	$page->register_dashboard();
}
