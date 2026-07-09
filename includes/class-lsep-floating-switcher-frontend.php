<?php
/**
 * Floating Switcher Frontend Renderer
 *
 * Handles the complete rendering and display of the floating language switcher
 * on the frontend. Manages asset loading, HTML generation, styling, and
 * integration with Polylang for language data.
 *
 * @package    Language_Switcher_For_Elementor_Polylang
 * @subpackage Language_Switcher_For_Elementor_Polylang/includes
 * @since      1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LSEP Floating Switcher Frontend Class
 *
 * Renders the floating language switcher on the frontend with full
 * support for responsive design, accessibility, and custom styling.
 *
 * @since 1.2.4
 */
class LSEP_Floating_Switcher_Frontend {

	/**
	 * Mobile layout breakpoint in pixels (matches admin preview).
	 *
	 * @since 1.2.5
	 * @var int
	 */
	const MOBILE_BREAKPOINT = 768;

	/**
	 * Switcher configuration array
	 *
	 * @since 1.2.4
	 * @var array|null
	 */
	private $config;

	/**
	 * Constructor
	 *
	 * Registers WordPress hooks for asset enqueuing and switcher rendering.
	 *
	 * @since 1.2.4
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_floater' ), 99 );
	}

	/**
	 * Get Switcher Configuration
	 *
	 * @since 1.2.4
	 * @return array Switcher configuration array
	 */
	private function get_config() {
		if ( null === $this->config ) {
			$this->config = get_option( 'lsep_floating_switcher_config', array() );
		}

		return $this->config;
	}

	/**
	 * Check if Floater is Enabled
	 *
	 * @since 1.2.4
	 * @return bool True if enabled, false otherwise
	 */
	private function is_enabled() {
		$config = $this->get_config();

		return ! empty( $config['enabled'] );
	}

	/**
	 * Enqueue Frontend Assets
	 *
	 * @since 1.2.4
	 */
	public function enqueue_assets() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$plugin_url = LSEP_PLUGIN_URL;
		$version    = defined( 'LSEP_VERSION' ) ? LSEP_VERSION : '1.0.0';

		wp_enqueue_style(
			'lsep-floating-switcher-frontend',
			$plugin_url . 'includes/css/lsep-floating-switcher-frontend.css',
			array(),
			$version
		);

		$config = $this->get_config();
		if ( ! empty( $config['enableCustomCss'] ) && ! empty( $config['customCss'] ) ) {
			$custom_css = wp_strip_all_tags( $config['customCss'] );
			$custom_css = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $custom_css );

			wp_add_inline_style( 'lsep-floating-switcher-frontend', $custom_css );
		}

		wp_enqueue_script(
			'lsep-floating-switcher-js',
			$plugin_url . 'includes/js/lsep-floating-switcher-frontend.js',
			array(),
			$version,
			true
		);

		wp_localize_script(
			'lsep-floating-switcher-js',
			'lsepFloaterFrontend',
			array(
				'mobileBreakpoint' => self::MOBILE_BREAKPOINT,
			)
		);
	}

	/**
	 * Render Floating Switcher
	 *
	 * @since 1.2.4
	 */
	public function render_floater() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! function_exists( 'pll_the_languages' ) || ! function_exists( 'pll_current_language' ) ) {
			return;
		}

		$config         = $this->get_config();
		$desktop_layout = $config['layoutCustomizer']['desktop'] ?? array();
		$mobile_layout  = $config['layoutCustomizer']['mobile'] ?? $desktop_layout;
		$languages      = LSEP_HELPERS::get_floater_languages_raw();

		if ( empty( $languages ) ) {
			return;
		}

		$is_dropdown = ( 'dropdown' === $config['type'] );
		$styles      = $this->build_responsive_switcher_styles( $config, $desktop_layout, $mobile_layout );

		$this->render_switcher_html(
			$languages,
			$config,
			$desktop_layout,
			$mobile_layout,
			$styles,
			$is_dropdown
		);
	}

	/**
	 * Build responsive CSS custom properties for desktop and mobile layouts.
	 *
	 * @since 1.2.5
	 * @param array $config         Switcher configuration.
	 * @param array $desktop_layout Desktop layout settings.
	 * @param array $mobile_layout  Mobile layout settings.
	 * @return string Inline CSS custom properties.
	 */
	private function build_responsive_switcher_styles( $config, $desktop_layout, $mobile_layout ) {
		$is_large     = ( 'large' === $config['size'] );
		$shared_vars  = array(
			'--bg'                  => $config['bgColor'],
			'--bg-hover'            => $config['bgHoverColor'],
			'--text'                => $config['textColor'],
			'--text-hover'          => $config['textHoverColor'],
			'--border-color'        => $config['borderColor'],
			'--border-width'        => $config['borderWidth'] . 'px',
			'--border-radius'       => $this->build_radius( $config['borderRadius'] ),
			'--flag-radius'         => $config['flagRadius'] . 'px',
			'--flag-size'           => $is_large ? '20px' : '18px',
			'--aspect-ratio'        => ( 'rect' === $config['flagShape'] ) ? '4/3' : '1',
			'--font-size'           => $is_large ? '16px' : '14px',
			'--transition-duration' => ! empty( $config['enableTransitions'] ) ? '0.2s' : '0s',
		);
		$desktop_vars = $this->build_layout_css_vars( 'desktop', $desktop_layout );
		$mobile_vars  = $this->build_layout_css_vars( 'mobile', $mobile_layout );
		$vars         = array_merge( $shared_vars, $desktop_vars, $mobile_vars );

		$style_pairs = array();
		foreach ( $vars as $key => $value ) {
			if ( preg_match( '/^--[a-z0-9-]+$/i', $key ) ) {
				$style_pairs[] = $key . ':' . esc_attr( $value );
			}
		}

		return implode( ';', $style_pairs );
	}

	/**
	 * Build viewport-specific layout CSS variables.
	 *
	 * @since 1.2.5
	 * @param string $viewport Desktop or mobile key prefix.
	 * @param array  $layout   Layout settings.
	 * @return array CSS variables.
	 */
	private function build_layout_css_vars( $viewport, $layout ) {
		$position       = $layout['position'] ?? 'bottom-right';
		$position_parts = explode( '-', $position );
		$vertical       = $position_parts[0] ?? 'bottom';
		$horizontal     = $position_parts[1] ?? 'right';

		return array(
			'--lsep-' . $viewport . '-top'           => ( 'top' === $vertical ) ? '0px' : 'auto',
			'--lsep-' . $viewport . '-bottom'        => ( 'bottom' === $vertical ) ? '0px' : 'auto',
			'--lsep-' . $viewport . '-right'         => ( 'right' === $horizontal ) ? '10%' : 'auto',
			'--lsep-' . $viewport . '-left'          => ( 'left' === $horizontal ) ? '10%' : 'auto',
			'--lsep-' . $viewport . '-width'         => ( 'custom' === ( $layout['width'] ?? 'default' ) ) ? absint( $layout['customWidth'] ?? 216 ) . 'px' : 'auto',
			'--lsep-' . $viewport . '-padding'       => ( 'custom' === ( $layout['padding'] ?? 'default' ) ) ? absint( $layout['customPadding'] ?? 0 ) . 'px' : '0',
			'--lsep-' . $viewport . '-vertical'      => $vertical,
		);
	}

	/**
	 * Get vertical position from a layout config.
	 *
	 * @since 1.2.5
	 * @param array $layout Layout settings.
	 * @return string top|bottom
	 */
	private function get_layout_vertical( $layout ) {
		$position = $layout['position'] ?? 'bottom-right';

		return explode( '-', $position )[0] ?? 'bottom';
	}

	/**
	 * Build Border Radius String
	 *
	 * @since 1.2.4
	 * @param array $radius_array Array of 4 radius values [TL, TR, BR, BL].
	 * @return string CSS border-radius value.
	 */
	private function build_radius( $radius_array ) {
		if ( ! is_array( $radius_array ) ) {
			return '8px 8px 0 0';
		}

		return implode(
			' ',
			array_map(
				function( $radius ) {
					return absint( $radius ) . 'px';
				},
				$radius_array
			)
		);
	}

	/**
	 * Render Switcher HTML
	 *
	 * @since 1.2.4
	 * @param array  $languages       Language objects.
	 * @param array  $config          Switcher configuration.
	 * @param array  $desktop_layout  Desktop layout settings.
	 * @param array  $mobile_layout   Mobile layout settings.
	 * @param string $styles          Inline CSS styles string.
	 * @param bool   $is_dropdown     Whether to render as dropdown.
	 */
	private function render_switcher_html( $languages, $config, $desktop_layout, $mobile_layout, $styles, $is_dropdown ) {
		$current = $languages[0] ?? null;
		$others  = array_slice( $languages, 1 );

		if ( ! $current ) {
			return;
		}

		$all_lang_names  = array_map(
			function( $lang ) use ( $desktop_layout ) {
				return LSEP_HELPERS::get_language_name( $lang['pll'], $desktop_layout['languageNames'] ?? 'full' );
			},
			$languages
		);
		$lang_names_json = esc_attr( wp_json_encode( $all_lang_names ) );
		$layout_class    = $is_dropdown ? 'lsep-ls-dropdown' : 'lsep-ls-inline';
		?>
		<nav class="lsep-language-switcher lsep-floating-switcher <?php echo esc_attr( $layout_class ); ?>"
			style="<?php echo esc_attr( $styles ); ?>"
			role="navigation"
			aria-label="<?php esc_attr_e( 'Website language selector', 'language-switcher-for-elementor-polylang' ); ?>"
			data-lang-names="<?php echo esc_attr( $lang_names_json ); ?>"
			data-lsep-desktop-vertical="<?php echo esc_attr( $this->get_layout_vertical( $desktop_layout ) ); ?>"
			data-lsep-mobile-vertical="<?php echo esc_attr( $this->get_layout_vertical( $mobile_layout ) ); ?>"
			data-no-translation>

			<?php if ( $is_dropdown ) : ?>
				<div class="lsep-language-switcher-inner">
					<?php $this->render_language_item( $current, true, $desktop_layout, $mobile_layout, $config ); ?>

					<?php if ( ! empty( $others ) ) : ?>
						<div class="lsep-switcher-dropdown-list"
							role="group"
							aria-label="<?php esc_attr_e( 'Available languages', 'language-switcher-for-elementor-polylang' ); ?>"
							hidden
							inert>
							<?php foreach ( $others as $lang ) : ?>
								<?php $this->render_language_item( $lang, false, $desktop_layout, $mobile_layout, $config ); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<div class="lsep-language-switcher-inner">
					<?php
					foreach ( $languages as $lang ) :
						$this->render_language_item( $lang, false, $desktop_layout, $mobile_layout, $config, $lang['is_current'] );
					endforeach;
					?>
				</div>
			<?php endif; ?>
		</nav>
		<?php
	}

	/**
	 * Render Language Item
	 *
	 * @since 1.2.4
	 * @param array  $lang            Language data array.
	 * @param bool   $as_control      Whether to render as button.
	 * @param array  $desktop_layout  Desktop layout settings.
	 * @param array  $mobile_layout   Mobile layout settings.
	 * @param array  $config          Switcher configuration.
	 * @param bool   $is_current      Whether this is the current active language.
	 */
	private function render_language_item( $lang, $as_control, $desktop_layout, $mobile_layout, $config, $is_current = false ) {
		$classes = array( 'lsep-language-item' );

		if ( $as_control ) {
			$classes[] = 'lsep-language-item__current';
		}

		if ( $is_current ) {
			$classes[] = 'lsep-language-item__default';
		}

		$tag = $as_control ? 'div' : 'a';
		?>
		<<?php echo esc_attr( $tag ); ?>
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			<?php if ( 'a' === $tag ) : ?>
				href="<?php echo esc_url( $lang['url'] ); ?>"
				title="<?php echo esc_attr( $lang['name'] ); ?>"
			<?php else : ?>
				role="button"
				tabindex="0"
				aria-expanded="false"
				aria-label="<?php esc_attr_e( 'Change language', 'language-switcher-for-elementor-polylang' ); ?>"
			<?php endif; ?>
			data-no-translation>
			<?php echo $this->render_responsive_language_labels( $lang, $desktop_layout, $mobile_layout, $config ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</<?php echo esc_attr( $tag ); ?>>
		<?php
	}

	/**
	 * Render responsive language labels and flag markup.
	 *
	 * @since 1.2.5
	 * @param array $lang            Language data.
	 * @param array $desktop_layout  Desktop layout settings.
	 * @param array $mobile_layout   Mobile layout settings.
	 * @param array $config          Switcher configuration.
	 * @return string HTML output.
	 */
	private function render_responsive_language_labels( $lang, $desktop_layout, $mobile_layout, $config ) {
		$html         = '';
		$desktop_name = LSEP_HELPERS::get_language_name( $lang['pll'], $desktop_layout['languageNames'] ?? 'full' );
		$mobile_name  = LSEP_HELPERS::get_language_name( $lang['pll'], $mobile_layout['languageNames'] ?? 'full' );
		$show_desktop = $this->should_show_language_names( $desktop_layout );
		$show_mobile  = $this->should_show_language_names( $mobile_layout );
		$flag_html    = $this->get_flag_html( $lang, $config );

		$html .= $this->render_flag_slot(
			$flag_html,
			$desktop_layout['flagIconPosition'] ?? 'before',
			$mobile_layout['flagIconPosition'] ?? 'before',
			'before'
		);

		if ( $show_desktop || $show_mobile ) {
			if ( $show_desktop && $show_mobile && $desktop_name === $mobile_name ) {
				$html .= '<span class="lsep-language-item-name">' . esc_html( $desktop_name ) . '</span>';
			} else {
				if ( $show_desktop ) {
					$html .= '<span class="lsep-language-item-name lsep-language-item-name-desktop">' . esc_html( $desktop_name ) . '</span>';
				}
				if ( $show_mobile ) {
					$html .= '<span class="lsep-language-item-name lsep-language-item-name-mobile">' . esc_html( $mobile_name ) . '</span>';
				}
			}
		}

		$html .= $this->render_flag_slot(
			$flag_html,
			$desktop_layout['flagIconPosition'] ?? 'before',
			$mobile_layout['flagIconPosition'] ?? 'before',
			'after'
		);

		return $html;
	}

	/**
	 * Render a viewport-aware flag slot.
	 *
	 * @since 1.2.5
	 * @param string $flag_html    Flag markup.
	 * @param string $desktop_flag Desktop flag position.
	 * @param string $mobile_flag  Mobile flag position.
	 * @param string $position     before|after.
	 * @return string
	 */
	private function render_flag_slot( $flag_html, $desktop_flag, $mobile_flag, $position ) {
		if ( empty( $flag_html ) ) {
			return '';
		}

		$classes = array( 'lsep-flag-slot' );

		if ( $position === $desktop_flag && 'hide' !== $desktop_flag ) {
			$classes[] = 'lsep-flag-slot-desktop-' . sanitize_html_class( $position );
		}

		if ( $position === $mobile_flag && 'hide' !== $mobile_flag ) {
			$classes[] = 'lsep-flag-slot-mobile-' . sanitize_html_class( $position );
		}

		if ( count( $classes ) === 1 ) {
			return '';
		}

		return '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $flag_html . '</span>';
	}

	/**
	 * Determine whether language names should be shown for a layout.
	 *
	 * @since 1.2.5
	 * @param array $layout Layout settings.
	 * @return bool
	 */
	private function should_show_language_names( $layout ) {
		return ! empty( $layout['languageNames'] ) && 'none' !== $layout['languageNames'];
	}

	/**
	 * Get Flag HTML
	 *
	 * @since 1.2.4
	 * @param array $lang   Language data array.
	 * @param array $config Switcher configuration.
	 * @return string Flag image HTML or empty string if no flag.
	 */
	private function get_flag_html( $lang, $config ) {
		if ( empty( $lang['flag'] ) ) {
			return '';
		}

		$shape_class = '';
		if ( 'square' === $config['flagShape'] ) {
			$shape_class = 'lsep-flag-square';
		} elseif ( 'rounded' === $config['flagShape'] ) {
			$shape_class = 'lsep-flag-rounded';
		}

		return sprintf(
			'<img src="%s" class="lsep-flag-image %s" alt="%s" loading="lazy" decoding="async" />',
			esc_url( $lang['flag'] ),
			esc_attr( $shape_class ),
			esc_attr( $lang['name'] )
		);
	}
}

new LSEP_Floating_Switcher_Frontend();
