<?php
/**
 * Main plugin class for Language Switcher Polylang Elementor integration.
 *
 * @package LanguageSwitcherManagerPolylangElementor
 * @since 1.0.0
 */

namespace LanguageSwitcherManagerPolylangElementor\LSEP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class LSEPManager
 *
 * Handles the integration between Polylang and Elementor for template translations.
 *
 * @since 1.0.0
 */
class LSEPManager {

    /**
     * Current template ID being processed.
     *
     * @var int|null
     */
    private $current_template_id;

    /**
     * Constructor.
     *
     * Initializes the plugin by setting up necessary hooks and filters.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter('pll_get_post_types', [$this, 'lsep_register_supported_post_types'], 10, 2);
        add_filter('elementor/theme/get_location_templates/template_id', [$this, 'lsep_translate_template_id']);
        add_filter('elementor/theme/get_location_templates/condition_sub_id', [$this, 'lsep_translate_condition_sub_id'], 10, 2);
        add_filter('pre_do_shortcode_tag', [$this, 'lsep_handle_shortcode_translation'], 10, 3);
        add_action('elementor/frontend/widget/before_render', [$this, 'lsep_translate_widget_template_id']);
        add_action('elementor/documents/register_controls', [$this, 'lsep_add_language_panel_controls']);

        if (is_plugin_active('elementor-pro/elementor-pro.php')) {
            add_action('set_object_terms', [$this, 'lsep_update_conditions_on_translation_change'], 10, 4);
        }
    }

    /**
     * Registers supported post types for Polylang translation.
     *
     * @since 1.0.0
     *
     * @param array $types        Array of post types.
     * @param bool  $is_settings  Whether this is called from settings page.
     * @return array Modified array of post types.
     */
    public function lsep_register_supported_post_types($types, $is_settings) {
        $custom_post_types = ['elementor_library'];
        return array_merge($types, array_combine($custom_post_types, $custom_post_types));
    }

    /**
     * Translates template ID based on current language.
     *
     * @since 1.0.0
     *
     * @param int $post_id The template post ID.
     * @return int Translated template ID.
     */
    public function lsep_translate_template_id($post_id) {
        // Get the language of the current page
        $page_lang = pll_get_post_language(get_the_ID());
        
        // Get the translated template in current page's language (if exists)
        $translated_post_id = pll_get_post($post_id, $page_lang);
    
        // If translated template exists, use it. Otherwise, fallback to default language template
        if ($translated_post_id) {
            $post_id = $translated_post_id;
        } else {
            // Fallback: get the template in the default language
            $default_lang = pll_default_language();
            $default_template_id = pll_get_post($post_id, $default_lang);
            
            if ($default_template_id) {
                $post_id = $default_template_id;
            }
            // Else fallback is original post_id (in case no default exists either)
        }
    
        $this->template_id = $post_id; // Save for later use
    
        return $post_id;
    }

    /**
     * Translates condition sub ID based on current language.
     *
     * @since 1.0.0
     *
     * @param int   $sub_id     The sub ID to translate.
     * @param array $condition  The condition data.
     * @return int Translated sub ID.
     */
    public function lsep_translate_condition_sub_id($sub_id, $condition) {
        if (!$sub_id) {
            return $sub_id;
        }

        $default_lang = pll_default_language();
        $current_lang = pll_get_post_language($this->current_template_id);

        if ($current_lang && $current_lang !== $default_lang && pll_get_post($this->current_template_id, $default_lang)) {
            if (in_array($condition['sub_name'], get_post_types(), true)) {
                $sub_id = pll_get_post($sub_id) ?: $sub_id;
            } else {
                $sub_id = pll_get_term($sub_id) ?: $sub_id;
            }
        }

        return $sub_id;
    }

    /**
     * Handles translation of Elementor template shortcodes.
     *
     * @since 1.0.0
     *
     * @param bool   $false  Whether to skip shortcode processing.
     * @param string $tag    Shortcode tag.
     * @param array  $attrs  Shortcode attributes.
     * @return string|bool Processed shortcode or false.
     */
    public function lsep_handle_shortcode_translation($false, $tag, $attrs) {
        if ('elementor-template' !== $tag || isset($attrs['skip'])) {
            return $false;
        }

        $attrs['id'] = pll_get_post(absint($attrs['id'])) ?: $attrs['id'];
        $attrs['skip'] = 1;

        $output = '';
        foreach ($attrs as $key => $value) {
            $output .= " $key=\"" . esc_attr($value) . "\"";
        }

        return do_shortcode('[elementor-template' . $output . ']');
    }

    /**
     * Updates conditions when translations change.
     *
     * @since 1.0.0
     *
     * @param int    $post_id  Post ID.
     * @param array  $terms    Terms.
     * @param array  $tt_ids   Term taxonomy IDs.
     * @param string $taxonomy Taxonomy name.
     */
    public function lsep_update_conditions_on_translation_change($post_id, $terms, $tt_ids, $taxonomy) {
        if ( 'post_translations' === $taxonomy && 'elementor_library' === get_post_type( $post_id ) ) {

			$theme_builder = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'theme-builder' );
			$theme_builder->get_conditions_manager()->get_cache()->regenerate();

		}
    }

    /**
     * Translates widget template ID.
     *
     * @since 1.0.0
     *
     * @param \Elementor\Element_Base $element Element instance.
     */
    public function lsep_translate_widget_template_id($element) {
        if ('template' !== $element->get_name()) {
            return;
        }

        $template_id = pll_get_post($element->get_settings('template_id')) ?: $element->get_settings('template_id');
        $element->set_settings('template_id', $template_id);
    }

    /**
     * Adds language panel controls to Elementor document.
     *
     * @since 1.0.0
     *
     * @param \Elementor\Core\Base\Document $document Document instance.
     */
    public function lsep_add_language_panel_controls($document) {
        if (!method_exists($document, 'get_main_id')) {
            return;
        }

        require_once LSEP_PLUGIN_DIR . 'helpers/class-lsep-helpers.php';

        $post_id = $document->get_main_id();
        $languages = pll_languages_list(['fields' => '']);
        $translations = pll_get_post_translations($post_id);
        $current_lang_name = pll_get_post_language($post_id, 'name');

        $document->start_controls_section(
            'lsep_language_panel_controls',
            [
                'label' => esc_html__('Translations', 'language-switcher-for-elementor-polylang'),
                'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
            ]
        );

        foreach ($languages as $lang) {
            $lang_slug = $lang->slug;
            if (isset($translations[$lang_slug])) {
                $translated_post_id = $translations[$lang_slug];
                $edit_link = get_edit_post_link($translated_post_id, 'edit');

                if (get_post_meta($translated_post_id, '_elementor_edit_mode', true)) {
                    $edit_link = add_query_arg('action', 'elementor', $edit_link);
                }

                $document->add_control(
                    "lsep_elementor_edit_lang_{$lang_slug}",
                    [
                        'type'            => \Elementor\Controls_Manager::RAW_HTML,
                        'raw'             => sprintf(
                            '<a href="%s" target="_blank"><i class="eicon-pencil"></i> %s — %s</a>',
                            esc_url($edit_link),
                            esc_html(get_the_title($translated_post_id)),
                            esc_html($lang->name)
                        ),
                        'content_classes' => 'elementor-control-field',
                    ]
                );
            } else {
                $create_link = add_query_arg([
                    'post_type' => get_post_type($post_id),
                    'from_post' => esc_attr($post_id),
                    'new_lang'  => esc_attr($lang_slug),
                    '_wpnonce'  => wp_create_nonce('new-post-translation'),
                ], admin_url('post-new.php'));

                $document->add_control(
                    "lsep_elementor_add_lang_{$lang_slug}",
                    [
                        'type'            => \Elementor\Controls_Manager::RAW_HTML,
                        'raw'             => sprintf(
                            '<a href="%s" target="_blank"><i class="eicon-plus"></i> %s</a>',
                            esc_url($create_link),
                            sprintf(
                                /* translators: %s: Language name */
                                __('Add translation — %s', 'language-switcher-for-elementor-polylang'),
                                esc_html($lang->name)
                            )
                        ),
                        'content_classes' => 'elementor-control-field',
                    ]
                );
            }
        }

        $document->end_controls_section();
    }
}

// Initialize the plugin
new LSEPManager();
