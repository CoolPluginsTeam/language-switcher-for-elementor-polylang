<?php
/**
 * Gutenberg Language Switcher Block
 *
 * @package Language_Switcher_For_Elementor_Polylang
 * @since   1.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class - Singleton pattern
 */
class LSEP_Language_Switcher_Block {

	/**
	 * Single instance of the class
	 *
	 * @var LSEP_Language_Switcher_Block
	 */
	private static $instance = null;

	/**
	 * Dropdown ID counter for unique dropdown IDs
	 *
	 * @var int
	 */
	private $dropdown_id = 0;

	/**
	 * Block ID counter for unique block IDs
	 *
	 * @var int
	 */
	private $block_id = 0;

	/**
	 * Private constructor to prevent direct instantiation
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Prevent cloning of the instance
	 *
	 * @throws Exception If clone is attempted.
	 */
	private function __clone() {
		throw new Exception( 'Cannot clone singleton' );
	}

	/**
	 * Prevent unserializing of the instance
	 *
	 * @throws Exception If unserialization is attempted.
	 */
	public function __wakeup() {
		throw new Exception( 'Cannot unserialize singleton' );
	}

	/**
	 * Get the singleton instance
	 *
	 * @return LSEP_Language_Switcher_Block
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_language_switcher_block' ) );
	}

	/**
	 * Register language switcher block
	 */
	public function register_language_switcher_block() {
		$this->register_block_assets();

		$switcher_options = $this->get_switcher_options();

		wp_localize_script(
			'lsep-language-switcher-block',
			'lsepBlockSettings',
			array(
				'options'        => $switcher_options,
				'languages'      => LSEP_HELPERS::get_polylang_language_select_options(),
				'polylangActive' => function_exists( 'PLL' ),
			)
		);

		$attributes = $this->get_block_attributes( $switcher_options );

		register_block_type(
			'lsep/language-switcher',
			array(
				'attributes'      => $attributes,
				'editor_script'   => 'lsep-language-switcher-block',
				'editor_style'    => 'lsep-language-switcher-block-editor',
				'style'           => 'lsep-language-switcher-block',
				'render_callback' => array( $this, 'render_language_switcher_block' ),
			)
		);
	}

	/**
	 * Register block assets (scripts and styles)
	 */
	private function register_block_assets() {
		$asset_file = LSEP_PLUGIN_DIR . 'blocks/language-switcher/build/index.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array(),
				'version'      => LSEP_VERSION,
			);

		wp_register_script(
			'lsep-language-switcher-block',
			LSEP_PLUGIN_URL . 'blocks/language-switcher/build/index.js',
			$asset['dependencies'],
			$asset['version'],
			false
		);

		wp_register_script(
			'lsep-custom-dropdown',
			LSEP_PLUGIN_URL . 'blocks/language-switcher/build/dropdown.js',
			array(),
			LSEP_VERSION,
			true
		);

		wp_register_style(
			'lsep-language-switcher-block-editor',
			LSEP_PLUGIN_URL . 'blocks/language-switcher/build/editor.css',
			array( 'wp-edit-blocks' ),
			LSEP_VERSION
		);

		wp_register_style(
			'lsep-language-switcher-block',
			LSEP_PLUGIN_URL . 'blocks/language-switcher/build/style.css',
			array(),
			LSEP_VERSION
		);

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_dropdown_script' ) );
	}

	/**
	 * Enqueue dropdown script in the block editor
	 */
	public function enqueue_editor_dropdown_script() {
		wp_enqueue_script( 'lsep-custom-dropdown' );
	}

	/**
	 * Get switcher options
	 *
	 * @return array
	 */
	private function get_switcher_options() {
		return array(
			'show_names'             => array(
				'label'   => __( 'Displays language names', 'language-switcher-for-elementor-polylang' ),
				'default' => 1,
			),
			'show_flags'             => array(
				'label'   => __( 'Displays flags', 'language-switcher-for-elementor-polylang' ),
				'default' => 0,
			),
			'show_language_codes'    => array(
				'label'   => __( 'Show Language Codes', 'language-switcher-for-elementor-polylang' ),
				'default' => 0,
			),
			'hide_current'           => array(
				'label'   => __( 'Hides the current language', 'language-switcher-for-elementor-polylang' ),
				'default' => 0,
			),
			'hide_if_no_translation' => array(
				'label'   => __( 'Hides languages with no translation', 'language-switcher-for-elementor-polylang' ),
				'default' => 0,
			),
		'dropdown'               => array(
			'label'   => __( 'Layout', 'language-switcher-for-elementor-polylang' ),
			'type'    => 'select',
			'default' => 'dropdown',
			'options' => array(
				'dropdown'   => __( 'Dropdown', 'language-switcher-for-elementor-polylang' ),
					'vertical'   => __( 'Vertical', 'language-switcher-for-elementor-polylang' ),
					'horizontal' => __( 'Horizontal', 'language-switcher-for-elementor-polylang' ),
				),
			),
		);
	}

	/**
	 * Get block attributes
	 *
	 * @param array $switcher_options Switcher options.
	 * @return array
	 */
	private function get_block_attributes( $switcher_options ) {
		$attributes = array(
			'className'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'marginTop'     => array(
				'type'    => 'number',
				'default' => 0,
			),
			'marginRight'   => array(
				'type'    => 'number',
				'default' => 0,
			),
			'marginBottom'  => array(
				'type'    => 'number',
				'default' => 0,
			),
			'marginLeft'    => array(
				'type'    => 'number',
				'default' => 0,
			),
			'paddingTop'    => array(
				'type'    => 'number',
				'default' => 0,
			),
			'paddingRight'  => array(
				'type'    => 'number',
				'default' => 0,
			),
			'paddingBottom' => array(
				'type'    => 'number',
				'default' => 0,
			),
			'paddingLeft'   => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderColor'   => array(
				'type'    => 'string',
				'default' => '',
			),
			'borderStyle'   => array(
				'type'    => 'string',
				'default' => 'solid',
			),
			'borderWidth'   => array(
				'type'    => 'string',
				'default' => '0px',
			),
			'borderWidthTop'    => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderWidthRight'  => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderWidthBottom' => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderWidthLeft'   => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderRadiusTopLeft'    => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderRadiusTopRight'   => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderRadiusBottomRight' => array(
				'type'    => 'number',
				'default' => 0,
			),
			'borderRadiusBottomLeft'  => array(
				'type'    => 'number',
				'default' => 0,
			),
			'flagRatio'     => array(
				'type'    => 'string',
				'default' => '4/3',
			),
			'flagWidth'     => array(
				'type'    => 'number',
				'default' => 24,
			),
			'flagRadius'    => array(
				'type'    => 'number',
				'default' => 0,
			),
			'fontSize'      => array(
				'type'    => 'string',
				'default' => '',
			),
			'fontFamily'    => array(
				'type'    => 'string',
				'default' => '',
			),
			'textColor'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'backgroundColor' => array(
				'type'    => 'string',
				'default' => '',
			),
			'textTransform' => array(
				'type'    => 'string',
				'default' => 'none',
			),
			'alignment' => array(
				'type'    => 'string',
				'default' => 'left',
			),
			'customLanguages' => array(
				'type'    => 'array',
				'default' => array(),
			),
			'languageSource' => array(
				'type'    => 'string',
				'default' => 'polylang',
			),
		);

		foreach ( $switcher_options as $option => $data ) {
			if ( isset( $data['type'] ) && 'select' === $data['type'] ) {
				$attributes[ $option ] = array(
					'type'    => 'string',
					'default' => $data['default'],
				);
			} else {
				$attributes[ $option ] = array(
					'type'    => 'boolean',
					'default' => (bool) $data['default'],
				);
			}
		}

		return $attributes;
	}

	/**
	 * Generate a unique block identifier
	 *
	 * @param array $attributes Block attributes.
	 * @return string Unique block identifier.
	 */
	private function get_unique_block_id( $attributes ) {
		++$this->block_id;
		$unique_hash = substr( md5( $this->block_id . wp_json_encode( $attributes ) ), 0, 8 );
		$unique_id   = 'lsep-block-' . $this->block_id . '-' . $unique_hash;
		return $unique_id;
	}

	/**
	 * Generate custom spacing, border, flag, and typography CSS
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $block_class Block class name.
	 * @return string Custom CSS.
	 */
	private function generate_spacing_css( $attributes, $block_class ) {
		$css = '';

		$margin  = lsep_get_block_spacing_values( $attributes, 'margin' );
		$padding = lsep_get_block_spacing_values( $attributes, 'padding' );
		$border  = lsep_get_block_border_values( $attributes );
		$flag    = lsep_get_block_flag_values( $attributes );

		$has_margin  = array_sum( $margin ) > 0;
		$has_padding = array_sum( $padding ) > 0;
		// Check if border has color and either unified width or individual widths
		$has_border  = ! empty( $border['color'] ) && ( ! empty( $border['width'] ) || $border['has_individual_widths'] );
		$has_border_radius = $border['has_border_radius'];
		$show_flags  = ! empty( $attributes['show_flags'] );
		
		// Typography attributes
		$has_font_size = ! empty( $attributes['fontSize'] );
		$has_font_family = ! empty( $attributes['fontFamily'] );
		$has_text_color = ! empty( $attributes['textColor'] );
		$has_background_color = ! empty( $attributes['backgroundColor'] );
		$has_text_transform = ! empty( $attributes['textTransform'] ) && $attributes['textTransform'] !== 'none';
		$has_typography = $has_font_size || $has_font_family || $has_text_color || $has_background_color || $has_text_transform;
		
		// Alignment (left/center/right).
		$alignment = isset( $attributes['alignment'] ) ? $attributes['alignment'] : 'left';
		$alignment = in_array( $alignment, array( 'left', 'center', 'right' ), true ) ? $alignment : 'left';
		$has_alignment = ( 'left' !== $alignment );

		if ( ! $has_margin && ! $has_padding && ! $has_border && ! $has_border_radius && ! $show_flags && ! $has_typography && ! $has_alignment ) {
			return '';
		}

		$css .= '<style>';

		// For horizontal/vertical layouts - list items
		$css .= '.' . $block_class . '.lsep-layout-horizontal .lsep-lang-item,';
		$css .= '.' . $block_class . '.lsep-layout-vertical .lsep-lang-item {';

		if ( $has_margin ) {
			$css .= 'margin: ' . implode( 'px ', $margin ) . 'px;';
		}

		if ( $has_padding ) {
			$css .= 'padding: ' . implode( 'px ', $padding ) . 'px;';
		}

		if ( $has_border ) {
			// Use individual border widths if available, otherwise use unified width
			if ( $border['has_individual_widths'] ) {
				$css .= 'border-color: ' . $border['color'] . ';';
				$css .= 'border-style: ' . $border['style'] . ';';
				$css .= 'border-width: ' . $border['top'] . 'px ' . $border['right'] . 'px ' . $border['bottom'] . 'px ' . $border['left'] . 'px;';
			} else {
				$css .= 'border: ' . $border['width'] . ' ' . $border['style'] . ' ' . $border['color'] . ';';
			}
		}

		if ( $has_border_radius ) {
			$css .= 'border-radius: ' . $border['radius_top_left'] . 'px ' . $border['radius_top_right'] . 'px ' . $border['radius_bottom_right'] . 'px ' . $border['radius_bottom_left'] . 'px;';
		}

		if ( $has_font_size ) {
			$css .= 'font-size: ' . esc_attr( $attributes['fontSize'] ) . ';';
		}

		if ( $has_font_family ) {
			$css .= 'font-family: ' . esc_attr( $attributes['fontFamily'] ) . ';';
		}

		if ( $has_text_color ) {
			$css .= 'color: ' . esc_attr( $attributes['textColor'] ) . '!important;';
		}

		if ( $has_background_color ) {
			$css .= 'background-color: ' . esc_attr( $attributes['backgroundColor'] ) . ';';
		}

		if ( $has_text_transform ) {
			$css .= 'text-transform: ' . esc_attr( $attributes['textTransform'] ) . ';';
		}

		$css .= '}';
		
		// Alignment rules.
		if ( $has_alignment ) {
			$justify = ( 'center' === $alignment ) ? 'center' : 'flex-end';
			$items   = ( 'center' === $alignment ) ? 'center' : 'flex-end';
			$text    = $alignment;

			// Horizontal: align the flex items across the row.
			$css .= '.' . $block_class . '.lsep-layout-horizontal {';
			$css .= 'justify-content: ' . $justify . ';';
			$css .= '}';

			// Vertical: align items horizontally within the column.
			$css .= '.' . $block_class . '.lsep-layout-vertical {';
			$css .= 'align-items: ' . $items . ';';
			$css .= '}';

			// Dropdown: align the inline-block dropdown container.
			$css .= '.' . $block_class . '.lsep-layout-dropdown {';
			$css .= 'text-align: ' . $text . ';';
			$css .= '}';
		}

	// For dropdown container - apply margin, padding, border, background, radius
	if ( $has_margin || $has_padding || $has_border || $has_border_radius || $has_background_color ) {
		$css .= '.' . $block_class . '.lsep-layout-dropdown .lsep-dropdown-container {';

		if ( $has_margin ) {
			$css .= 'margin: ' . implode( 'px ', $margin ) . 'px !important;';
		}

		if ( $has_padding ) {
			$css .= 'padding: ' . implode( 'px ', $padding ) . 'px !important;';
		}

		if ( $has_border ) {
			// Use individual border widths if available, otherwise use unified width
			if ( $border['has_individual_widths'] ) {
				$css .= 'border-color: ' . $border['color'] . ' !important;';
				$css .= 'border-style: ' . $border['style'] . ' !important;';
				$css .= 'border-width: ' . $border['top'] . 'px ' . $border['right'] . 'px ' . $border['bottom'] . 'px ' . $border['left'] . 'px !important;';
			} else {
				$css .= 'border: ' . $border['width'] . ' ' . $border['style'] . ' ' . $border['color'] . ' !important;';
			}
		}

		if ( $has_border_radius ) {
			$css .= 'border-radius: ' . $border['radius_top_left'] . 'px ' . $border['radius_top_right'] . 'px ' . $border['radius_bottom_right'] . 'px ' . $border['radius_bottom_left'] . 'px !important;';
		}

		if ( $has_background_color ) {
			$css .= 'background-color: ' . esc_attr( $attributes['backgroundColor'] ) . ' !important;';
		}

		$css .= '}';
	}

	// For dropdown button - only typography styles
	if ( $has_font_size || $has_font_family || $has_text_color || $has_text_transform ) {
		$css .= '.' . $block_class . '.lsep-layout-dropdown .lsep-dropdown-button {';

		if ( $has_font_size ) {
			$css .= 'font-size: ' . esc_attr( $attributes['fontSize'] ) . ' !important;';
		}

		if ( $has_font_family ) {
			$css .= 'font-family: ' . esc_attr( $attributes['fontFamily'] ) . ' !important;';
		}

		if ( $has_text_color ) {
			$css .= 'color: ' . esc_attr( $attributes['textColor'] ) . ' !important;';
		}

		if ( $has_text_transform ) {
			$css .= 'text-transform: ' . esc_attr( $attributes['textTransform'] ) . ' !important;';
		}

		$css .= '}';
	}

	// For dropdown menu (ul) - background color, padding, and border radius
	if ( $has_background_color || $has_padding || $has_border_radius ) {
		$css .= '.' . $block_class . '.lsep-layout-dropdown .lsep-dropdown-menu {';
		
		if ( $has_background_color ) {
			$css .= 'background-color: ' . esc_attr( $attributes['backgroundColor'] ) . ' !important;';
		}
		
		if ( $has_border_radius ) {
			$css .= 'border-radius: ' . $border['radius_top_left'] . 'px ' . $border['radius_top_right'] . 'px ' . $border['radius_bottom_right'] . 'px ' . $border['radius_bottom_left'] . 'px !important;';
		}
		
		$css .= '}';
	}

	// For dropdown menu items - padding only
	if ( $has_padding ) {
		$css .= '.' . $block_class . '.lsep-layout-dropdown .lsep-dropdown-item {';
		$css .= 'padding: ' . implode( 'px ', $padding ) . 'px !important;';
		$css .= '}';
	}

	// For dropdown menu items - typography
	if ( $has_typography ) {
		$css .= '.' . $block_class . '.lsep-layout-dropdown .lsep-dropdown-item a {';

		if ( $has_font_size ) {
			$css .= 'font-size: ' . esc_attr( $attributes['fontSize'] ) . ';';
		}

		if ( $has_font_family ) {
			$css .= 'font-family: ' . esc_attr( $attributes['fontFamily'] ) . ';';
		}

		if ( $has_text_color ) {
			$css .= 'color: ' . esc_attr( $attributes['textColor'] ) . '!important;';
		}

		if ( $has_text_transform ) {
			$css .= 'text-transform: ' . esc_attr( $attributes['textTransform'] ) . ';';
		}

		$css .= '}';
	}

		// Flag styles
		if ( $show_flags ) {
			$flag_height = $flag['ratio'] === '4/3' ? round( $flag['width'] * 0.75 ) : $flag['width'];

			$css .= '.' . $block_class . ' .lsep-lang-image {';
			$css .= 'width: ' . $flag['width'] . 'px;';
			$css .= 'height: ' . $flag_height . 'px;';
			$css .= 'overflow: hidden;';
			$css .= '}';

			$css .= '.' . $block_class . ' .lsep-lang-image img {';
			$css .= 'width: 100%;';
			$css .= 'height: 100%;';
			$css .= 'object-fit: cover;';
			if ( $flag['radius'] ) {
				$css .= 'border-radius: ' . $flag['radius'] . 'px;';
			}
			$css .= '}';
		}

		$css .= '</style>';

		return $css;
	}

	/**
	 * Get current language slug in both frontend and editor contexts
	 *
	 * @return string Current language slug.
	 */
	private function get_current_language_slug() {
		if ( ! function_exists( 'pll_current_language' ) ) {
			return '';
		}

		// Try to get post language in editor/REST context.
		global $post;
		
		$post_id = null;

		// Check if we're in a REST API request (editor context).
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			// Try to extract post ID from REST route.
			if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
				// Match patterns like /wp/v2/posts/123 or /wp/v2/pages/123.
				if ( preg_match( '#/wp/v2/(?:posts|pages|[^/]+)/(\d+)#', sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $matches ) ) {
					$post_id = intval( $matches[1] );
				}
			}

			// Check $_GET for post_id.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only operation to determine language context in editor, not processing form data.
			if ( ! $post_id && ! empty( $_GET['post_id'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only operation to determine language context in editor, not processing form data.
				$post_id = intval( $_GET['post_id'] );
			}

			// If we found a post ID, get its language.
			if ( $post_id && function_exists( 'pll_get_post_language' ) ) {
				$lang = pll_get_post_language( $post_id );
				if ( $lang ) {
					return $lang;
				}
			}
		}

		// If we have a post object and it has a language, use that.
		if ( isset( $post->ID ) && function_exists( 'pll_get_post_language' ) ) {
			$lang = pll_get_post_language( $post->ID );
			if ( $lang ) {
				return $lang;
			}
		}

		// Fallback to pll_current_language() for frontend.
		return pll_current_language();
	}

	/**
	 * Render language switcher block
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	public function render_language_switcher_block( $attributes ) {
		// Get the language source preference
		$language_source = isset( $attributes['languageSource'] ) ? $attributes['languageSource'] : 'polylang';
		
		// If language source is set to 'default', use custom languages
		if ( 'default' === $language_source ) {
			$custom_languages = isset( $attributes['customLanguages'] ) ? $attributes['customLanguages'] : array();
			
			// Check if custom languages have valid data (at least language must be selected)
			if ( ! empty( $custom_languages ) ) {
				$has_valid_custom = false;
				foreach ( $custom_languages as $lang ) {
					if ( ! empty( $lang['language'] ) ) {
						$has_valid_custom = true;
						break;
					}
				}
				if ( $has_valid_custom ) {
					return $this->render_custom_languages( $attributes );
				}
			}
			
			// If no valid custom languages, show nothing
			return '';
		}
		
		// If language source is 'polylang', use Polylang
	if ( ! function_exists( 'pll_the_languages' ) ) {
		// If Polylang is not available, fall back to default language
		return $this->render_default_language( $attributes );
	}

	$layout              = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : 'dropdown';
		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		if ( ! $show_names && ! $show_flags && ! $show_language_codes ) {
			return '';
		}

		if ( 'dropdown' === $layout ) {
			return $this->render_custom_dropdown( $attributes );
		}

		return $this->render_horizontal_vertical_layout( $attributes );
	}

	/**
	 * Render default language (English) when Polylang is not available
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	private function render_default_language( $attributes ) {
		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		// Check if at least one display option is enabled.
		if ( ! $show_names && ! $show_flags && ! $show_language_codes ) {
			return '';
		}

		// Get current page URL.
		$current_url = home_url();
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$current_url = esc_url_raw( home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		}

		// Create default English language array.
		$default_language = array(
			array(
				'slug'   => 'en',
				'name'   => 'English',
				'url'    => $current_url,
			'flag'   => 'us',
			'locale' => 'en_US',
		),
	);

	$layout = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : 'dropdown';

		// Use existing rendering methods.
		if ( 'dropdown' === $layout ) {
			return $this->render_dropdown_layout( $attributes, $default_language, 'en' );
		}

		return $this->render_list_layout( $attributes, $default_language, 'en' );
	}

	/**
	 * Convert custom languages to Polylang-compatible format
	 *
	 * @param array $custom_languages Custom languages array.
	 * @return array Languages in Polylang format.
	 */
	private function convert_custom_languages_to_polylang_format( $custom_languages ) {
		$converted = array();
		$seen      = array();

		$current_url = home_url();
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$current_url = esc_url_raw( home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
		}

		foreach ( $custom_languages as $custom_lang ) {
			if ( empty( $custom_lang['language'] ) ) {
				continue;
			}

			$lang_key = $custom_lang['language'];

			if ( isset( $seen[ $lang_key ] ) ) {
				continue;
			}
			$seen[ $lang_key ] = true;

			$lang_data = LSEP_HELPERS::get_polylang_language_by_slug( $lang_key );
			if ( ! $lang_data ) {
				continue;
			}

			$url = ! empty( $custom_lang['url'] ) ? $custom_lang['url'] : $current_url;

			$converted[] = array(
				'slug'   => $lang_data['slug'],
				'name'   => $lang_data['name'],
				'url'    => $url,
				'flag'   => ! empty( $lang_data['flag_url'] ) ? $lang_data['flag_url'] : '',
				'locale' => $lang_data['locale'],
			);
		}

		return $converted;
	}

	/**
	 * Render custom languages from repeater field
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	private function render_custom_languages( $attributes ) {
		$custom_languages = isset( $attributes['customLanguages'] ) ? $attributes['customLanguages'] : array();
		
		if ( empty( $custom_languages ) ) {
			return '';
		}

		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		// Check if at least one display option is enabled
		if ( ! $show_names && ! $show_flags && ! $show_language_codes ) {
			return '';
		}

		// Convert custom languages to Polylang format
		$languages = $this->convert_custom_languages_to_polylang_format( $custom_languages );
	
	if ( empty( $languages ) ) {
		return '';
	}

	$layout = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : 'dropdown';

		// Use existing rendering methods
		if ( 'dropdown' === $layout ) {
			return $this->render_dropdown_layout( $attributes, $languages );
		}

		return $this->render_list_layout( $attributes, $languages );
	}

	/**
	 * Render horizontal and vertical layouts (for Polylang)
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	private function render_horizontal_vertical_layout( $attributes ) {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}

		$show_names             = ! empty( $attributes['show_names'] );
		$hide_current           = ! empty( $attributes['hide_current'] );
		$hide_if_no_translation = ! empty( $attributes['hide_if_no_translation'] );

		// Get current language slug (works in both frontend and editor contexts)
		$current_lang_slug = $this->get_current_language_slug();

		$args = array(
			'echo'                   => 0,
			'raw'                    => 1,
			'show_flags'             => 0,
			'show_names'             => $show_names,
			'hide_current'           => false,
			'hide_if_no_translation' => false,
		);

		$languages = pll_the_languages( $args );

		if ( empty( $languages ) || ! is_array( $languages ) ) {
			return '';
		}

		// Manually filter languages using our correct language detection (needed for editor/REST context)
		if ( $hide_current || $hide_if_no_translation ) {
			$languages = array_filter(
				$languages,
				function( $lang ) use ( $hide_current, $hide_if_no_translation, $current_lang_slug ) {
					// Filter out current language if hide_current is enabled
					if ( $hide_current && isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug ) {
						return false;
					}
					// Filter out languages with no translation if hide_if_no_translation is enabled
					if ( $hide_if_no_translation && ! empty( $lang['no_translation'] ) ) {
						return false;
					}
					return true;
				}
			);
		}

		return $this->render_list_layout( $attributes, $languages, $current_lang_slug );
	}

	/**
	 * Render list layout (horizontal/vertical) - reusable for both Polylang and custom languages
	 *
	 * @param array  $attributes Block attributes.
	 * @param array  $languages Languages array in Polylang format.
	 * @param string $current_lang_slug Optional current language slug.
	 * @return string Block HTML.
	 */
private function render_list_layout( $attributes, $languages, $current_lang_slug = null ) {
	$layout              = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : 'dropdown';
		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		$unique_class       = $this->get_unique_block_id( $attributes );
		$layout_class       = 'lsep-layout-' . esc_attr( $layout );
		$custom_class       = isset( $attributes['className'] ) ? $attributes['className'] : '';
		$wrapper_class      = trim( $unique_class . ' ' . $layout_class . ' ' . $custom_class );
		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_class ) );
		$aria_label         = __( 'Choose a language', 'language-switcher-for-elementor-polylang' );
		$spacing_css        = $this->generate_spacing_css( $attributes, $unique_class );
		$switcher_output    = '';

		foreach ( $languages as $lang ) {
			$is_current = isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug;

			$switcher_output .= '<li class="lsep-lang-item' . ( $is_current ? ' current-lang' : '' ) . '">';

			$link_attrs = array(
				'href' => esc_url( $lang['url'] ),
			);

			if ( $is_current ) {
				$link_attrs['aria-current'] = 'true';
			}

			if ( ! empty( $lang['locale'] ) ) {
				$link_attrs['lang']     = esc_attr( $lang['locale'] );
				$link_attrs['hreflang'] = esc_attr( $lang['locale'] );
			}

			$switcher_output .= '<a';
			foreach ( $link_attrs as $attr => $value ) {
				$switcher_output .= ' ' . $attr . '="' . $value . '"';
			}
			$switcher_output .= '>';

			if ( $show_flags ) {
				$custom_flag = LSEP_HELPERS::lsep_get_country_flag( $lang['flag'], $lang['name'] );
				if ( $custom_flag ) {
					$switcher_output .= '<div class="lsep-lang-image">' . $custom_flag . '</div>';
				}
			}

			if ( $show_names && ! empty( $lang['name'] ) ) {
				$switcher_output .= '<div class="lsep-lang-name">' . esc_html( $lang['name'] ) . '</div>';
			}

			if ( $show_language_codes && ! empty( $lang['slug'] ) ) {
				$switcher_output .= '<div class="lsep-lang-code">' . esc_html( $lang['slug'] ) . '</div>';
			}

			$switcher_output .= '</a></li>';
		}

		return sprintf(
			'%s<nav role="navigation" aria-label="%s"><ul %s>%s</ul></nav>',
			$spacing_css,
			esc_attr( $aria_label ),
			$wrapper_attributes,
			$switcher_output
		);
	}

	/**
	 * Render custom dropdown (for Polylang)
	 *
	 * @param array $attributes Block attributes.
	 * @return string Block HTML.
	 */
	private function render_custom_dropdown( $attributes ) {
		if ( ! function_exists( 'pll_the_languages' ) ) {
			return '';
		}

		$show_names             = ! empty( $attributes['show_names'] );
		$hide_current           = ! empty( $attributes['hide_current'] );
		$hide_if_no_translation = ! empty( $attributes['hide_if_no_translation'] );

		// Get current language slug (works in both frontend and editor contexts)
		$current_lang_slug = $this->get_current_language_slug();

		$args = array(
			'echo'                   => 0,
			'raw'                    => 1,
			'show_flags'             => 0,
			'show_names'             => $show_names,
			'hide_current'           => false,
			'hide_if_no_translation' => false,
		);

		$languages = pll_the_languages( $args );

		if ( empty( $languages ) || ! is_array( $languages ) ) {
			return '';
		}

		// Manually filter languages using our correct language detection (needed for editor/REST context)
		if ( $hide_current || $hide_if_no_translation ) {
			$languages = array_filter(
				$languages,
				function( $lang ) use ( $hide_current, $hide_if_no_translation, $current_lang_slug ) {
					// Filter out current language if hide_current is enabled
					if ( $hide_current && isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug ) {
						return false;
					}
					// Filter out languages with no translation if hide_if_no_translation is enabled
					if ( $hide_if_no_translation && ! empty( $lang['no_translation'] ) ) {
						return false;
					}
					return true;
				}
			);
		}

		return $this->render_dropdown_layout( $attributes, $languages, $current_lang_slug );
	}

	/**
	 * Render dropdown layout - reusable for both Polylang and custom languages
	 *
	 * @param array  $attributes Block attributes.
	 * @param array  $languages Languages array in Polylang format.
	 * @param string $current_lang_slug Optional current language slug.
	 * @return string Block HTML.
	 */
	private function render_dropdown_layout( $attributes, $languages, $current_lang_slug = null ) {
		$show_names          = ! empty( $attributes['show_names'] );
		$show_flags          = ! empty( $attributes['show_flags'] );
		$show_language_codes = ! empty( $attributes['show_language_codes'] );

		wp_enqueue_script( 'lsep-custom-dropdown' );

		$unique_class = $this->get_unique_block_id( $attributes );
		$dropdown_id  = ++$this->dropdown_id;
		$unique_id    = 'lsep-dropdown-' . $dropdown_id . '-' . substr( $unique_class, -8 );

		// Find current language
		$current_lang = null;
		if ( $current_lang_slug ) {
			foreach ( $languages as $lang ) {
				if ( isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug ) {
					$current_lang = $lang;
					break;
				}
			}
		}

		if ( ! $current_lang ) {
			$current_lang = reset( $languages );
		}

		// If no valid current language found, return empty
		if ( ! is_array( $current_lang ) || empty( $current_lang ) ) {
			return '';
		}

		$layout_class       = 'lsep-layout-dropdown lsep-custom-dropdown';
		$custom_class       = isset( $attributes['className'] ) ? $attributes['className'] : '';
		$wrapper_class      = trim( $unique_class . ' ' . $layout_class . ' ' . $custom_class );
		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $wrapper_class ) );
		$aria_label         = __( 'Choose a language', 'language-switcher-for-elementor-polylang' );
		$spacing_css        = $this->generate_spacing_css( $attributes, $unique_class );

		$output  = $spacing_css;
		$output .= '<div ' . $wrapper_attributes . '>';
		$output .= '<div class="lsep-dropdown-container" id="' . esc_attr( $unique_id ) . '">';
		$output .= '<button type="button" class="lsep-dropdown-button lsep-lang-item" aria-haspopup="listbox" aria-expanded="false" aria-label="' . esc_attr( $aria_label ) . '">';

		if ( $show_flags ) {
			$custom_flag = LSEP_HELPERS::lsep_get_country_flag( $current_lang['flag'], $current_lang['name'] );
			if ( $custom_flag ) {
				$output .= '<div class="lsep-lang-image">' . $custom_flag . '</div>';
			}
		}

		if ( $show_names && ! empty( $current_lang['name'] ) ) {
			$output .= '<div class="lsep-dropdown-button-name">' . esc_html( $current_lang['name'] );
			if ( $show_language_codes && ! empty( $current_lang['slug'] ) ) {
				$output .= ' <span class="lsep-language-code">' . esc_html( $current_lang['slug'] ) . '</span>';
			}
			$output .= '</div>';
		} elseif ( $show_language_codes && ! empty( $current_lang['slug'] ) ) {
			$output .= '<div class="lsep-dropdown-button-name"><span class="lsep-language-code">' . esc_html( $current_lang['slug'] ) . '</span></div>';
		}

	$output .= '<span class="lsep-dropdown-arrow" aria-hidden="true">▼</span>';
	$output .= '</button>';
	$output .= '<ul class="lsep-dropdown-menu" role="listbox" style="display: none;">';

	foreach ( $languages as $lang ) {
		$is_current = isset( $lang['slug'] ) && $lang['slug'] === $current_lang_slug;
		
		// Skip the language that's being shown in the button
		// This handles both the actual current language and the first alternative when hide_current is enabled
		if ( $is_current || ( isset( $lang['slug'] ) && isset( $current_lang['slug'] ) && $lang['slug'] === $current_lang['slug'] ) ) {
			continue;
		}

		$classes = array( 'lsep-dropdown-item' );

		$output .= '<li role="option" class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		$output .= '<a href="' . esc_url( $lang['url'] ) . '">';

			if ( $show_flags ) {
				$custom_flag = LSEP_HELPERS::lsep_get_country_flag( $lang['flag'], $lang['name'] );
				if ( $custom_flag ) {
					$output .= '<div class="lsep-lang-image">' . $custom_flag . '</div>';
				}
			}

			if ( $show_names && ! empty( $lang['name'] ) ) {
				$output .= '<div class="lsep-dropdown-item-name">' . esc_html( $lang['name'] );
				if ( $show_language_codes && ! empty( $lang['slug'] ) ) {
					$output .= ' <span class="lsep-language-code">' . esc_html( $lang['slug'] ) . '</span>';
				}
				$output .= '</div>';
			} elseif ( $show_language_codes && ! empty( $lang['slug'] ) ) {
				$output .= '<div class="lsep-dropdown-item-name"><span class="lsep-language-code">' . esc_html( $lang['slug'] ) . '</span></div>';
			}

			$output .= '</a></li>';
		}

		$output .= '</ul></div></div>';

		return $output;
	}
}

