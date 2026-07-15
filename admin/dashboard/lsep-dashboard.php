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
		echo '<a href="' . esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/#new-topic-0' ) . '" class="button button-secondary lsep-header-btn lsep-header-btn-support" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-editor-help lsep-header-btn-question-icon" aria-hidden="true"></span><span class="lsep-header-btn-label">' . esc_html__( 'Get Support', 'language-switcher-for-elementor-polylang' ) . '</span></a>';
		echo '<a href="' . esc_url( 'https://docs.coolplugins.net/doc/language-switcher-for-elementor-polylang/?utm_source=lsep_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_header' ) . '" class="button button-secondary lsep-header-btn" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-book" aria-hidden="true"></span><span class="lsep-header-btn-label">' . esc_html__( 'Documentation', 'language-switcher-for-elementor-polylang' ) . '</span></a>';
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
		require_once $this->addon_dir . '/includes/autopoly-promo.php';
		require $this->addon_dir . '/includes/get-started-content.php';
	}

	/**
	 * Builder guides for the Get Started tab.
	 *
	 * @return array
	 */
	private function get_started_builder_data() {
		$video_id  = 'HyM0woo9Cg0';
		$embed_url = 'https://www.youtube.com/embed/' . $video_id;
		$plus_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M11 12.5V17.5H12.5V12.5H17.5V11H12.5V6H11V11H6V12.5H11Z"></path></svg>';

		return array(
			'elementor' => array(
				'guideTitle' => __( 'Elementor Quick Start Guide', 'language-switcher-for-elementor-polylang' ),
				'guideSub'   => __( 'Follow these simple steps to add and configure the Language Switcher widget in Elementor.', 'language-switcher-for-elementor-polylang' ),
				'steps'      => array(
					array(
						'title' => __( 'Add Language Switcher Widget', 'language-switcher-for-elementor-polylang' ),
						'items' => array(
							__( 'Open a page using Elementor.', 'language-switcher-for-elementor-polylang' ),
							__( 'Search for "Language Switcher" in the widgets panel.', 'language-switcher-for-elementor-polylang' ),
							__( 'Drag and drop the widget where you want to show the switcher.', 'language-switcher-for-elementor-polylang' ),
						),
					),
					array(
						'title'     => __( 'Translate Elementor Templates', 'language-switcher-for-elementor-polylang' ),
						'items'     => array(
							__( 'From your WordPress dashboard, go to Templates > Saved Templates.', 'language-switcher-for-elementor-polylang' ),
							__( 'Create a new template in Elementor.', 'language-switcher-for-elementor-polylang' ),
							__( 'Translate your Elementor templates via Polylang.', 'language-switcher-for-elementor-polylang' ),
						),
						'button'    => __( 'Go to Template Settings', 'language-switcher-for-elementor-polylang' ),
						'buttonUrl' => admin_url( 'edit.php?post_type=elementor_library' ),
					),
					array(
						'title' => __( 'Translations Control Panel', 'language-switcher-for-elementor-polylang' ),
						'items' => array(
							__( 'Manage and edit translated versions of your pages using the Translations Control Panel.', 'language-switcher-for-elementor-polylang' ),
							__( 'Click the Edit icon to modify an existing translation.', 'language-switcher-for-elementor-polylang' ),
							__( 'Click the Create icon to quickly start a translation.', 'language-switcher-for-elementor-polylang' ),
						),
					),
				),
				'embedUrl'   => $embed_url,
			),
			'gutenberg' => array(
				'guideTitle' => __( 'Gutenberg Quick Start Guide', 'language-switcher-for-elementor-polylang' ),
				'guideSub'   => __( 'Follow these simple steps to add and configure the Language Switcher block in Gutenberg.', 'language-switcher-for-elementor-polylang' ),
				'steps'      => array(
					array(
						'title' => __( 'Add Language Switcher Block', 'language-switcher-for-elementor-polylang' ),
						'items' => array(
							__( 'Open a page or post in the Block Editor.', 'language-switcher-for-elementor-polylang' ),
							sprintf(
								/* translators: %s: Gutenberg inserter plus icon */
								__( 'Click %s and search for "Language Switcher".', 'language-switcher-for-elementor-polylang' ),
								$plus_icon
							),
							__( 'Insert the block wherever you want the switcher to appear.', 'language-switcher-for-elementor-polylang' ),
						),
					),
					array(
						'title'     => __( 'Translate Gutenberg Content', 'language-switcher-for-elementor-polylang' ),
						'items'     => array(
							__( 'From your WordPress dashboard, open the page in the editor.', 'language-switcher-for-elementor-polylang' ),
							__( 'Use the Polylang language box to start a new translation.', 'language-switcher-for-elementor-polylang' ),
							__( 'Translate each block\'s content directly inside the editor.', 'language-switcher-for-elementor-polylang' ),
						),
						'button'    => __( 'Go to Pages', 'language-switcher-for-elementor-polylang' ),
						'buttonUrl' => admin_url( 'edit.php?post_type=page' ),
					),
					array(
						'title' => __( 'Translations Control Panel', 'language-switcher-for-elementor-polylang' ),
						'items' => array(
							__( 'Manage and edit translated versions of your pages using the Translations Control Panel.', 'language-switcher-for-elementor-polylang' ),
							__( 'Click the Edit icon to modify an existing translation.', 'language-switcher-for-elementor-polylang' ),
							__( 'Click the Create icon to quickly start a translation.', 'language-switcher-for-elementor-polylang' ),
						),
					),
				),
				'embedUrl'   => $embed_url,
			),
			'divi'      => array(
				'guideTitle' => __( 'Divi Quick Start Guide', 'language-switcher-for-elementor-polylang' ),
				'guideSub'   => __( 'Follow these simple steps to add and configure the Language Switcher module in Divi.', 'language-switcher-for-elementor-polylang' ),
				'steps'      => array(
					array(
						'title' => __( 'Add Language Switcher Module', 'language-switcher-for-elementor-polylang' ),
						'items' => array(
							__( 'Open a page using the Divi Builder.', 'language-switcher-for-elementor-polylang' ),
							sprintf(
								/* translators: %s: Divi inserter plus icon */
								__( 'Click %s to insert a new module.', 'language-switcher-for-elementor-polylang' ),
								$plus_icon
							),
							__( 'Search for "Language Switcher" and drop it into your layout.', 'language-switcher-for-elementor-polylang' ),
						),
					),
					array(
						'title'     => __( 'Translate Divi Layouts', 'language-switcher-for-elementor-polylang' ),
						'items'     => array(
							__( 'From your WordPress dashboard, go to Divi > Theme Builder.', 'language-switcher-for-elementor-polylang' ),
							__( 'Duplicate your layout for each language.', 'language-switcher-for-elementor-polylang' ),
							__( 'Translate your Divi layouts via Polylang.', 'language-switcher-for-elementor-polylang' ),
						),
						'button'    => __( 'Go to Theme Builder', 'language-switcher-for-elementor-polylang' ),
						'buttonUrl' => admin_url( 'admin.php?page=et_theme_builder' ),
					),
					array(
						'title' => __( 'Translations Control Panel', 'language-switcher-for-elementor-polylang' ),
						'items' => array(
							__( 'Manage and edit translated versions of your pages using the Translations Control Panel.', 'language-switcher-for-elementor-polylang' ),
							__( 'Click the Edit icon to modify an existing translation.', 'language-switcher-for-elementor-polylang' ),
							__( 'Click the Create icon to quickly start a translation.', 'language-switcher-for-elementor-polylang' ),
						),
					),
				),
				'embedUrl'   => $embed_url,
			),
		);
	}

	/**
	 * Enqueue dashboard styles and Get Started assets.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_required_scripts( $hook ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for conditional asset loading.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for conditional asset loading.
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'getting-started';

		if ( 'lsep-get-started' !== $page && false === strpos( $hook, 'lsep-get-started' ) ) {
                return;
            }

		wp_enqueue_style(
			'cool-lsep-plugins-polylang-addon',
			plugin_dir_url( __FILE__ ) . 'assets/css/styles.css',
			array( 'dashicons' ),
			LSEP_VERSION,
			'all'
		);

		require_once $this->addon_dir . '/includes/autopoly-promo.php';
		lsep_enqueue_autopoly_promo_script();

		if ( 'floating-switcher' === $tab ) {
			return;
		}

		wp_enqueue_script(
			'lsep-get-started',
			plugin_dir_url( __FILE__ ) . 'assets/js/get-started.js',
			array( 'lsep-autopoly-promo' ),
			LSEP_VERSION,
			true
		);

		wp_localize_script(
			'lsep-get-started',
			'lsepGetStarted',
			array(
				'builders' => $this->get_started_builder_data(),
			)
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
