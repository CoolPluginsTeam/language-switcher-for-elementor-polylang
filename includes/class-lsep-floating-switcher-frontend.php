<?php
/**
 * Floating Switcher Frontend Renderer
 * 
 * Handles rendering and display of the floating language switcher on the frontend
 */

if (!defined('ABSPATH')) {
    exit;
}

class LSEP_Floating_Switcher_Frontend {
    
    private $config;
    private $viewport;
    
    public function __construct() {
        $this->viewport = wp_is_mobile() ? 'mobile' : 'desktop';
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_floater'], 99);
    }
    
    /**
     * Get switcher configuration
     */
    private function get_config() {
        if ($this->config === null) {
            $this->config = get_option('lsep_floating_switcher_config', []);
        }
        return $this->config;
    }
    
    /**
     * Check if floater is enabled
     */
    private function is_enabled() {
        $config = $this->get_config();
        return !empty($config['enabled']);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        if (!$this->is_enabled()) {
            return;
        }
        
        $plugin_url = plugin_dir_url(dirname(__FILE__));
        $version = defined('LSEP_VERSION') ? LSEP_VERSION : '1.0.0';
        
        // Frontend CSS
        wp_enqueue_style(
            'lsep-floating-switcher-frontend',
            $plugin_url . 'includes/css/lsep-floating-switcher-frontend.css',
            [],
            $version
        );
        
        // Frontend JavaScript
        wp_enqueue_script(
            'lsep-floating-switcher-js',
            $plugin_url . 'includes/js/lsep-floating-switcher-frontend.js',
            [],
            $version,
            true
        );
    }
    
    /**
     * Render the floating switcher in footer
     */
    public function render_floater() {
        if (!$this->is_enabled()) {
            return;
        }
        
        // Check if Polylang is active
        if (!function_exists('pll_the_languages') || !function_exists('pll_current_language')) {
            return;
        }
        
        $config = $this->get_config();
        $layout = $config['layoutCustomizer'][$this->viewport] ?? $config['layoutCustomizer']['desktop'];
        
        // Get languages from Polylang
        $languages = $this->get_polylang_languages($config, $layout);
        
        if (empty($languages)) {
            return;
        }
        
        $is_dropdown = $config['type'] === 'dropdown';
        
        // Build styles
        $styles = $this->build_switcher_styles($config, $layout);
        
        // Position class
        $position_class = strpos($layout['position'], 'top') !== false 
            ? 'lsep-switcher-position-top' 
            : 'lsep-switcher-position-bottom';
        
        // Render the switcher
        $this->render_switcher_html($languages, $config, $layout, $styles, $position_class, $is_dropdown);
        
        // Output custom CSS if enabled
        if (!empty($config['enableCustomCss']) && !empty($config['customCss'])) {
            echo '<style id="lsep-floating-switcher-custom-css">' . esc_html(wp_strip_all_tags($config['customCss'])) . '</style>';
        }
    }
    
    /**
     * Get languages from Polylang
     */
    private function get_polylang_languages($config, $layout) {
        $current_lang = pll_current_language();
        $languages = [];
        
        // Get raw languages
        $raw_languages = pll_the_languages([
            'raw' => 1,
            'hide_if_empty' => 0,
        ]);
        
        if (empty($raw_languages)) {
            return [];
        }
        
        // Process languages
        foreach ($raw_languages as $lang) {
            $lang_data = [
                'code' => $lang['slug'],
                'name' => $this->get_language_name($lang, $layout['languageNames']),
                'url' => $lang['url'],
                'flag' => $this->get_plugin_flag_url($lang['flag'] ?? ''),
                'is_current' => $lang['slug'] === $current_lang,
            ];
            
            // Put current language first
            if ($lang_data['is_current']) {
                array_unshift($languages, $lang_data);
            } else {
                $languages[] = $lang_data;
            }
        }
        
        return $languages;
    }
    
    /**
     * Get language name based on display mode
     */
    private function get_language_name($lang, $mode) {
        switch ($mode) {
            case 'full':
                return $lang['name'];
            case 'short':
                return strtoupper($lang['slug']);
            case 'none':
            default:
                return '';
        }
    }

    /**
 * Get plugin's SVG flag URL using existing helper
 */
private function get_plugin_flag_url($polylang_flag_url) {
    if (empty($polylang_flag_url)) {
        return '';
    }
    
    // Use existing helper to get flag code
    $country_code = LSEP_HELPERS::lsep_get_flag_code($polylang_flag_url);
    
    if ($country_code) {
        // Return plugin's SVG flag URL
        return LSEP_PLUGIN_URL . 'assets/flags/' . esc_attr($country_code) . '.svg';
    }
    
    // Fallback to original Polylang flag
    return $polylang_flag_url;
}
    
    /**
     * Build inline styles for the switcher
     */
    private function build_switcher_styles($config, $layout) {
        $position = $layout['position'];
        $is_large = $config['size'] === 'large';
        
        // Parse position for all 4 positioning variables (like admin preview JS)
        $position_parts = explode('-', $position);
        $vertical = $position_parts[0] ?? 'bottom';
        $horizontal = $position_parts[1] ?? 'right';
        
        // Position vars - all 4 like admin preview
        $position_vars = [
                '--bottom' => $vertical === 'bottom' ? '0px' : 'auto',
                '--top' => $vertical === 'top' ? '0px' : 'auto',
                '--right' => $horizontal === 'right' ? '10%' : 'auto',
                '--left' => $horizontal === 'left' ? '10%' : 'auto',
            ];
        
        $vars = array_merge([
            '--bg' => $config['bgColor'],
            '--bg-hover' => $config['bgHoverColor'],
            '--text' => $config['textColor'],
            '--text-hover' => $config['textHoverColor'],
            '--border-color' => $config['borderColor'],
            '--border-width' => $config['borderWidth'] . 'px',
            '--border-radius' => $this->build_radius($config['borderRadius']),
            '--flag-radius' => "{$config['flagRadius']}px",
            '--flag-size' => $is_large ? '20px' : '18px',
            '--aspect-ratio' => $config['flagShape'] === 'rect' ? '4/3' : '1',
            '--font-size' => $is_large ? '16px' : '14px',
            '--switcher-width' => $layout['width'] === 'custom' ? "{$layout['customWidth']}px" : 'auto',
            '--switcher-padding' => $layout['padding'] === 'custom' ? "{$layout['customPadding']}px" : '0',
            '--transition-duration' => $config['enableTransitions'] ? '0.2s' : '0s',
        ], $position_vars);
        // Build style string
        $style_pairs = [];
        foreach ($vars as $key => $value) {
            if (preg_match('/^--[a-z0-9-]+$/i', $key)) {
                $style_pairs[] = $key . ':' . esc_attr($value);
            }
        }
        
        return implode(';', $style_pairs);
    }
    
    /**
     * Build border radius string
     */
    private function build_radius($radius_array) {
        if (!is_array($radius_array)) {
            return '8px 8px 0 0';
        }
        return implode(' ', array_map(function($r) {
            return intval($r) . 'px';
        }, $radius_array));
    }
    
    /**
     * Render the switcher HTML
     */
    private function render_switcher_html($languages, $config, $layout, $styles, $position_class, $is_dropdown) {
        $current = $languages[0] ?? null;
        $others = array_slice($languages, 1);
        
        if (!$current) {
            return;
        }
        
        $flag_position = $layout['flagIconPosition'];
        ?>
        <nav class="lsep-language-switcher lsep-floating-switcher <?php echo $is_dropdown ? 'lsep-ls-dropdown ' : 'lsep-ls-inline '; echo esc_attr($position_class); ?>"
             style="<?php echo esc_attr($styles); ?>"
             role="navigation"
             aria-label="<?php esc_attr_e('Website language selector', 'language-switcher-for-elementor-polylang'); ?>"
             data-no-translation>
            
            
            <?php if ($is_dropdown) : ?>
                <div class="lsep-language-switcher-inner">
                    <?php 
                    // Current language (as control if not opposite mode)
                    $this->render_language_item($current, true, $flag_position, $config); 
                    ?>
                    
                    <?php if (!empty($others)) : ?>
                        <div class="lsep-switcher-dropdown-list"
                             role="group"
                             aria-label="<?php esc_attr_e('Available languages', 'language-switcher-for-elementor-polylang'); ?>"
                             hidden
                             inert>
                            <?php foreach ($others as $lang) : ?>
                                <?php $this->render_language_item($lang, false, $flag_position, $config); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : // Side by side ?>
                <div class="lsep-language-switcher-inner">
        <?php 
        // Show ALL languages side by side
        foreach ($languages as $lang) :
            $this->render_language_item($lang, true, $flag_position, $config, $lang['is_current']);
        endforeach;
        ?>
    </div>
<?php endif; ?>
        </nav>
        <?php
    }
    
    /**
     * Render a single language item
     */
    private function render_language_item($lang, $as_control, $flag_position, $config, $is_current = false) {
        $classes = ['lsep-language-item'];
        
        if ($as_control) {
            $classes[] = 'lsep-language-item__current';
        }
        
        if ($is_current) {
            $classes[] = 'lsep-language-item__default';
        }
        
        $tag = $as_control ? 'div' : 'a';
        $flag_html = $this->get_flag_html($lang, $config);
        
        ?>
        <<?php echo esc_attr($tag); ?> 
            class="<?php echo esc_attr(implode(' ', $classes)); ?>"
            <?php if ($tag === 'a') : ?>
                href="<?php echo esc_url($lang['url']); ?>"
                title="<?php echo esc_attr($lang['name']); ?>"
            <?php else : ?>
                role="button"
                tabindex="0"
                aria-expanded="false"
                aria-label="<?php esc_attr_e('Change language', 'language-switcher-for-elementor-polylang'); ?>"
            <?php endif; ?>
            data-no-translation>
            
            <?php if ($flag_position === 'before' && $flag_html) : ?>
                <?php echo $flag_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
            
            <?php if (!empty($lang['name'])) : ?>
                <span class="lsep-language-item-name"><?php echo esc_html($lang['name']); ?></span>
            <?php endif; ?>
            
            <?php if ($flag_position === 'after' && $flag_html) : ?>
                <?php echo $flag_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </<?php echo esc_attr($tag); ?>>
        <?php
    }
    
    /**
     * Get flag HTML
     */
    private function get_flag_html($lang, $config) {
        if (empty($lang['flag'])) {
            return '';
        }
        
        $shape_class = '';
        if ($config['flagShape'] === 'square') {
            $shape_class = 'lsep-flag-square';
        } elseif ($config['flagShape'] === 'rounded') {
            $shape_class = 'lsep-flag-rounded';
        }
        
        return sprintf(
            '<img src="%s" class="lsep-flag-image %s" alt="%s" loading="lazy" decoding="async" />',
            esc_url($lang['flag']),
            esc_attr($shape_class),
            esc_attr($lang['name'])
        );
    }
}

// Initialize
new LSEP_Floating_Switcher_Frontend();