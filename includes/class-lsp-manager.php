<?php
namespace LanguageSwitcherPolylangElementor\LSP;

class LSPManager {

    public function __construct() {
        add_filter('pll_get_post_types', [$this, 'lsp_register_supported_post_types'], 10, 2);
        add_filter('elementor/theme/get_location_templates/template_id', [$this, 'lsp_translate_template_id']);
        add_filter('elementor/theme/get_location_templates/condition_sub_id', [$this, 'lsp_translate_condition_sub_id'], 10, 2);
        add_filter('pre_do_shortcode_tag', [$this, 'lsp_handle_shortcode_translation'], 10, 3);
        add_action('elementor/frontend/widget/before_render', [$this, 'lsp_translate_widget_template_id']);
        add_action('elementor/documents/register_controls', [$this, 'lsp_add_language_panel_controls']);

        if (is_plugin_active('elementor-pro/elementor-pro.php')) {
            add_action('set_object_terms', [$this, 'lsp_update_conditions_on_translation_change'], 10, 4);
        }
    }

    public function lsp_register_supported_post_types($types, $is_settings) {
        $custom_post_types = ['elementor_library'];
        return array_merge($types, array_combine($custom_post_types, $custom_post_types));
    }

    public function lsp_translate_template_id($post_id) {
        $translated_id = pll_get_post($post_id);
        $this->current_template_id = $translated_id ?: $post_id;
        return $this->current_template_id;
    }

    public function lsp_translate_condition_sub_id($sub_id, $condition) {
        if (!$sub_id) return $sub_id;

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

    public function lsp_handle_shortcode_translation($false, $tag, $attrs) {
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

    public function lsp_update_conditions_on_translation_change($post_id, $terms, $tt_ids, $taxonomy) {
        if ('post_translations' === $taxonomy && get_post_type($post_id) === 'elementor_library') {
            $theme_module = \ElementorPro\Plugin::instance()->modules_manager->get_modules('theme-builder');
            $theme_module->get_conditions_manager()->get_cache()->regenerate();
        }
    }

    public function lsp_translate_widget_template_id($element) {
        if ('template' !== $element->get_name()) return;

        $template_id = pll_get_post($element->get_settings('template_id')) ?: $element->get_settings('template_id');
        $element->set_settings('template_id', $template_id);
    }

    public function lsp_add_language_panel_controls($document) {
        if (!method_exists($document, 'get_main_id')) return;

        require_once LSP_PLUGIN_DIR . 'helpers/class-lsp-helpers.php';

        $post_id = $document->get_main_id();
        $languages = pll_languages_list(['fields' => '']);
        $translations = pll_get_post_translations($post_id);
        $current_lang_name = pll_get_post_language($post_id, 'name');

        $document->start_controls_section(
            'lsp_language_panel_controls',
            [
                'label' => esc_html__('Translations', 'language-switcher-elementor'),
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
                    "lsp_elementor_edit_lang_{$lang_slug}",
                    [
                        'type'    => \Elementor\Controls_Manager::RAW_HTML,
                        'raw'     => sprintf(
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
                    'from_post' => $post_id,
                    'new_lang'  => $lang_slug,
                    '_wpnonce'  => wp_create_nonce('new-post-translation'),
                ], admin_url('post-new.php'));

                $document->add_control(
                    "lsp_elementor_add_lang_{$lang_slug}",
                    [
                        'type'    => \Elementor\Controls_Manager::RAW_HTML,
                        'raw'     => sprintf(
                            '<a href="%s" target="_blank"><i class="eicon-plus"></i> %s</a>',
                            esc_url($create_link),
                            sprintf(__('Add translation — %s', 'language-switcher-elementor'), esc_html($lang->name))
                        ),
                        'content_classes' => 'elementor-control-field',
                    ]
                );
            }
        }

        $document->end_controls_section();
    }
}

new LSPManager();
