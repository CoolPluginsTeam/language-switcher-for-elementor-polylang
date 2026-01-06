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
    exit; // Exit if accessed directly
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
     * Switcher configuration array
     *
     * @since 1.2.4
     * @var array|null
     */
    private $config;
    
    /**
     * Current viewport type (mobile or desktop)
     *
     * @since 1.2.4
     * @var string
     */
    private $viewport;
    
    /**
     * Constructor
     *
     * Detects viewport type and registers WordPress hooks for
     * asset enqueuing and switcher rendering.
     *
     * @since 1.2.4
     */
    public function __construct() {
        // Detect viewport type (mobile or desktop)
        $this->viewport = wp_is_mobile() ? 'mobile' : 'desktop';
        
        // Enqueue frontend CSS and JavaScript
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        
        // Render switcher in footer (priority 99 for late rendering)
        add_action( 'wp_footer', [ $this, 'render_floater' ], 99 );
    }
    
    /**
     * Get Switcher Configuration
     *
     * Retrieves the switcher configuration from database with lazy loading.
     * Caches the result in the instance property to avoid multiple database queries.
     *
     * @since 1.2.4
     * @return array Switcher configuration array
     */
    private function get_config() {
        if ( $this->config === null ) {
            $this->config = get_option( 'lsep_floating_switcher_config', [] );
        }
        return $this->config;
    }
    
    /**
     * Check if Floater is Enabled
     *
     * Determines whether the floating switcher should be displayed
     * based on the configuration settings.
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
     * Loads CSS and JavaScript files for the floating switcher.
     * Also adds custom CSS inline if enabled in settings.
     *
     * @since 1.2.4
     */
public function enqueue_assets() {
    // Only enqueue if switcher is enabled
    if ( ! $this->is_enabled() ) {
        return;
    }
    
    $plugin_url = LSEP_PLUGIN_URL;
    $version    = defined( 'LSEP_VERSION' ) ? LSEP_VERSION : '1.0.0';
    
    // Enqueue frontend CSS
    wp_enqueue_style(
        'lsep-floating-switcher-frontend',
        $plugin_url . 'includes/css/lsep-floating-switcher-frontend.css',
        [],
        $version
    );
    
    // Add custom CSS inline if enabled in settings
    $config = $this->get_config();
    if ( ! empty( $config['enableCustomCss'] ) && ! empty( $config['customCss'] ) ) {
        // Sanitize CSS content for security
        $custom_css = wp_strip_all_tags( $config['customCss'] );
        $custom_css = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $custom_css );
        
        wp_add_inline_style( 'lsep-floating-switcher-frontend', $custom_css );
    }
    
    // Enqueue frontend JavaScript for interactions
    wp_enqueue_script(
        'lsep-floating-switcher-js',
        $plugin_url . 'includes/js/lsep-floating-switcher-frontend.js',
        [],
        $version,
        true // Load in footer
    );
}
    
    /**
     * Render Floating Switcher
     *
     * Main rendering method that outputs the floating language switcher HTML
     * in the footer. Checks for Polylang availability and configuration,
     * then generates the complete switcher markup with styles.
     *
     * @since 1.2.4
     */
    public function render_floater() {
        // Only render if enabled
        if ( ! $this->is_enabled() ) {
            return;
        }
        
        // Check if Polylang functions are available
        if ( ! function_exists( 'pll_the_languages' ) || ! function_exists( 'pll_current_language' ) ) {
            return;
        }
        
        $config = $this->get_config();
        // Get layout config for current viewport (mobile/desktop)
        $layout = $config['layoutCustomizer'][ $this->viewport ] ?? $config['layoutCustomizer']['desktop'];
        
        // Get available languages from Polylang
        $languages = $this->get_polylang_languages( $config, $layout );
        
        // Don't render if no languages available
        if ( empty( $languages ) ) {
            return;
        }
        
        // Determine switcher type (dropdown or side-by-side)
        $is_dropdown = $config['type'] === 'dropdown';
        
        // Build inline CSS styles for the switcher
        $styles = $this->build_switcher_styles( $config, $layout );
        
        // Determine position class based on layout
        $position_class = strpos( $layout['position'], 'top' ) !== false 
            ? 'lsep-switcher-position-top' 
            : 'lsep-switcher-position-bottom';
        
        // Render the complete switcher HTML
        $this->render_switcher_html( $languages, $config, $layout, $styles, $position_class, $is_dropdown );
        
       // Output custom CSS in footer if enabled
if ( ! empty( $config['enableCustomCss'] ) && ! empty( $config['customCss'] ) ) {
    // Sanitize CSS: remove script tags and dangerous content for security
    $custom_css = wp_strip_all_tags( $config['customCss'] );
    $custom_css = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $custom_css );
    
    // Output custom CSS safely in a style tag
    echo '<style id="lsep-floating-switcher-custom-css">';
    echo wp_kses( $custom_css, array(
        'style' => array(),
    ) );
    echo '</style>';
}
    }
    
    /**
     * Get Polylang Languages
     *
     * Retrieves and processes language data from Polylang.
     * Formats each language with code, name, URL, flag, and current status.
     * Places the current language first in the array.
     *
     * @since 1.2.4
     * @param array $config Switcher configuration
     * @param array $layout Layout configuration for current viewport
     * @return array Array of formatted language objects
     */
    private function get_polylang_languages( $config, $layout ) {
        $current_lang = pll_current_language();
        $languages    = [];
        
        // Get raw language data from Polylang
        $raw_languages = pll_the_languages([
            'raw'           => 1, // Return raw data
            'hide_if_empty' => 0, // Show all languages
        ]);
        
        if ( empty( $raw_languages ) ) {
            return [];
        }
        
        // Process and format each language
        foreach ( $raw_languages as $lang ) {
            $lang_data = [
                'code'       => $lang['slug'],
                'name'       => \LSEP_HELPERS::get_language_name( $lang, $layout['languageNames'] ),
                'url'        => $lang['url'],
                'flag'       => \LSEP_HELPERS::get_plugin_flag_url( $lang['flag'] ?? '' ),
                'is_current' => $lang['slug'] === $current_lang,
            ];
            
            // Put current language first in the array for proper display order
            if ( $lang_data['is_current'] ) {
                array_unshift( $languages, $lang_data );
            } else {
                $languages[] = $lang_data;
            }
        }
        
        return $languages;
    }
    
    /**
     * Get Language Name
     *
     * Returns the language name formatted according to the display mode setting.
     *
     * @since 1.2.4
     * @param array  $lang Language data from Polylang
     * @param string $mode Display mode ('full', 'short', or 'none')
     * @return string Formatted language name
     */
    private function get_language_name( $lang, $mode ) {
        switch ( $mode ) {
            case 'full':
                return $lang['name'];
            case 'short':
                return strtoupper( $lang['slug'] );
            case 'none':
            default:
                return '';
        }
    }

    /**
     * Get Plugin Flag URL
     *
     * Converts Polylang flag URL to plugin's SVG flag URL using existing helper.
     * Falls back to original Polylang flag if conversion fails.
     *
     * @since 1.2.4
     * @param string $polylang_flag_url URL to Polylang's flag image
     * @return string URL to flag image
     */
    private function get_plugin_flag_url( $polylang_flag_url ) {
        if ( empty( $polylang_flag_url ) ) {
            return '';
        }
        
        // Extract country code from Polylang flag URL using helper
        $country_code = \LSEP_HELPERS::lsep_get_flag_code( $polylang_flag_url );
        
        if ( $country_code ) {
            // Return plugin's SVG flag URL
            $plugin_url = LSEP_PLUGIN_URL;
            return $plugin_url . 'assets/flags/' . esc_attr( $country_code ) . '.svg';
        }
        
        // Fallback to original Polylang flag if conversion fails
        return $polylang_flag_url;
    }
    
    /**
     * Build Switcher Styles
     *
     * Generates inline CSS custom properties for the switcher based on
     * configuration settings. Includes colors, sizing, positioning,
     * border radius, transitions, and viewport-specific layout.
     *
     * @since 1.2.4
     * @param array $config Switcher configuration
     * @param array $layout Layout configuration for current viewport
     * @return string CSS custom properties as inline style string
     */
    private function build_switcher_styles( $config, $layout ) {
        $position = $layout['position'];
        $is_large = $config['size'] === 'large';
        
        // Parse position string into vertical and horizontal components
        $position_parts = explode( '-', $position );
        $vertical       = $position_parts[0] ?? 'bottom';
        $horizontal     = $position_parts[1] ?? 'right';
        
        // Build position CSS variables (all 4 directions for proper positioning)
        $position_vars = [
            '--bottom' => $vertical === 'bottom' ? '0px' : 'auto',
            '--top'    => $vertical === 'top' ? '0px' : 'auto',
            '--right'  => $horizontal === 'right' ? '10%' : 'auto',
            '--left'   => $horizontal === 'left' ? '10%' : 'auto',
        ];
        
        // Merge all CSS custom properties
        $vars = array_merge([
            '--bg'                  => $config['bgColor'],
            '--bg-hover'            => $config['bgHoverColor'],
            '--text'                => $config['textColor'],
            '--text-hover'          => $config['textHoverColor'],
            '--border-color'        => $config['borderColor'],
            '--border-width'        => $config['borderWidth'] . 'px',
            '--border-radius'       => $this->build_radius( $config['borderRadius'] ),
            '--flag-radius'         => "{$config['flagRadius']}px",
            '--flag-size'           => $is_large ? '20px' : '18px',
            '--aspect-ratio'        => $config['flagShape'] === 'rect' ? '4/3' : '1',
            '--font-size'           => $is_large ? '16px' : '14px',
            '--switcher-width'      => $layout['width'] === 'custom' ? "{$layout['customWidth']}px" : 'auto',
            '--switcher-padding'    => $layout['padding'] === 'custom' ? "{$layout['customPadding']}px" : '0',
            '--transition-duration' => $config['enableTransitions'] ? '0.2s' : '0s',
        ], $position_vars );

        // Build inline style string from CSS variables
        $style_pairs = [];
        foreach ( $vars as $key => $value ) {
            // Validate CSS variable name format
            if ( preg_match( '/^--[a-z0-9-]+$/i', $key ) ) {
                $style_pairs[] = $key . ':' . esc_attr( $value );
            }
        }
        
        return implode( ';', $style_pairs );
    }
    
    /**
     * Build Border Radius String
     *
     * Converts border radius array (4 corners) into CSS border-radius value.
     * Returns default value if array is invalid.
     *
     * @since 1.2.4
     * @param array $radius_array Array of 4 radius values [TL, TR, BR, BL]
     * @return string CSS border-radius value (e.g., "8px 8px 0 0")
     */
    private function build_radius( $radius_array ) {
        if ( ! is_array( $radius_array ) ) {
            return '8px 8px 0 0'; // Default radius
        }
        // Convert each value to pixels and join with spaces
        return implode( ' ', array_map( function( $r ) {
            return intval( $r ) . 'px';
        }, $radius_array ) );
    }
    
    /**
     * Render Switcher HTML
     *
     * Outputs the complete HTML markup for the language switcher.
     * Handles both dropdown and side-by-side display modes with
     * proper accessibility attributes and semantic HTML.
     *
     * @since 1.2.4
     * @param array  $languages       Array of formatted language objects
     * @param array  $config          Switcher configuration
     * @param array  $layout          Layout configuration for current viewport
     * @param string $styles          Inline CSS styles string
     * @param string $position_class  CSS class for positioning
     * @param bool   $is_dropdown     Whether to render as dropdown or side-by-side
     */
    private function render_switcher_html( $languages, $config, $layout, $styles, $position_class, $is_dropdown ) {
        // Split languages: current language first, others in dropdown/list
        $current = $languages[0] ?? null;
        $others  = array_slice( $languages, 1 );
        
        if ( ! $current ) {
            return; // No languages available
        }
        
        // Prepare flag position and language names data
        $flag_position   = $layout['flagIconPosition'];
        $all_lang_names  = array_map( function( $lang ) { return $lang['name']; }, $languages );
        $lang_names_json = esc_attr( json_encode( $all_lang_names ) );
        ?>
       <nav class="lsep-language-switcher lsep-floating-switcher <?php echo $is_dropdown ? 'lsep-ls-dropdown ' : 'lsep-ls-inline '; echo esc_attr( $position_class ); ?>"
     style="<?php echo esc_attr( $styles ); ?>"
     role="navigation"
     aria-label="<?php esc_attr_e( 'Website language selector', 'language-switcher-for-elementor-polylang' ); ?>"
     data-lang-names="<?php echo esc_attr( $lang_names_json ); ?>"
     data-no-translation>
            
            <?php if ( $is_dropdown ) : // Dropdown mode ?>
                <div class="lsep-language-switcher-inner">
                    <?php 
                    // Render current language as the dropdown control button
                    $this->render_language_item( $current, true, $flag_position, $config ); 
                    ?>
                    
                    <?php if ( ! empty( $others ) ) : ?>
                        <!-- Dropdown list of other languages -->
                        <div class="lsep-switcher-dropdown-list"
                             role="group"
                             aria-label="<?php esc_attr_e( 'Available languages', 'language-switcher-for-elementor-polylang' ); ?>"
                             hidden
                             inert>
                            <?php foreach ( $others as $lang ) : ?>
                                <?php $this->render_language_item( $lang, false, $flag_position, $config ); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : // Side-by-side mode ?>
                <div class="lsep-language-switcher-inner">
                    <?php 
                    // Show all languages inline (side by side)
                    foreach ( $languages as $lang ) :
                        $this->render_language_item( $lang, false, $flag_position, $config, $lang['is_current'] );
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
     * Outputs HTML for a single language link or button.
     * Handles flag positioning, accessibility attributes, and
     * different rendering modes (control button vs link).
     *
     * @since 1.2.4
     * @param array  $lang         Language data array
     * @param bool   $as_control   Whether to render as button (true) or link (false)
     * @param string $flag_position Flag position ('before', 'after', or 'hide')
     * @param array  $config       Switcher configuration
     * @param bool   $is_current   Whether this is the current active language
     */
    private function render_language_item( $lang, $as_control, $flag_position, $config, $is_current = false ) {
        // Build CSS classes for the language item
        $classes = [ 'lsep-language-item' ];
        
        if ( $as_control ) {
            $classes[] = 'lsep-language-item__current';
        }
        
        if ( $is_current ) {
            $classes[] = 'lsep-language-item__default';
        }
        
        // Use div for control button, anchor for regular links
        $tag       = $as_control ? 'div' : 'a';
        $flag_html = $this->get_flag_html( $lang, $config );
        
        ?>
        <<?php echo esc_attr( $tag ); ?> 
            class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
            <?php if ( $tag === 'a' ) : // Regular language link ?>
                href="<?php echo esc_url( $lang['url'] ); ?>"
                title="<?php echo esc_attr( $lang['name'] ); ?>"
            <?php else : // Dropdown control button ?>
                role="button"
                tabindex="0"
                aria-expanded="false"
                aria-label="<?php esc_attr_e( 'Change language', 'language-switcher-for-elementor-polylang' ); ?>"
            <?php endif; ?>
            data-no-translation>
            
            <?php if ( $flag_position === 'before' && $flag_html ) : // Flag before text ?>
                <?php echo $flag_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
            
            <?php if ( ! empty( $lang['name'] ) ) : // Language name ?>
                <span class="lsep-language-item-name"><?php echo esc_html( $lang['name'] ); ?></span>
            <?php endif; ?>
            
            <?php if ( $flag_position === 'after' && $flag_html ) : // Flag after text ?>
                <?php echo $flag_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </<?php echo esc_attr( $tag ); ?>>
        <?php
    }
    
    /**
     * Get Flag HTML
     *
     * Generates HTML markup for a language flag image with
     * appropriate shape classes and accessibility attributes.
     *
     * @since 1.2.4
     * @param array $lang   Language data array
     * @param array $config Switcher configuration
     * @return string Flag image HTML or empty string if no flag
     */
    private function get_flag_html( $lang, $config ) {
        if ( empty( $lang['flag'] ) ) {
            return '';
        }
        
        // Determine flag shape class based on configuration
        $shape_class = '';
        if ( $config['flagShape'] === 'square' ) {
            $shape_class = 'lsep-flag-square';
        } elseif ( $config['flagShape'] === 'rounded' ) {
            $shape_class = 'lsep-flag-rounded';
        }
        
        // Build and return flag image HTML
        return sprintf(
            '<img src="%s" class="lsep-flag-image %s" alt="%s" loading="lazy" decoding="async" />',
            esc_url( $lang['flag'] ),
            esc_attr( $shape_class ),
            esc_attr( $lang['name'] )
        );
    }
}

/**
 * Initialize Floating Switcher Frontend
 *
 * Create an instance of the frontend class to register hooks and render the switcher.
 *
 * @since 1.2.4
 */
new LSEP_Floating_Switcher_Frontend();