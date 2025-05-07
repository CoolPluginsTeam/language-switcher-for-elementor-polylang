<?php
namespace LanguageSwitcherPolylangElementor\LSP;

class LSPManager
{
    public function __construct()
    {
        add_filter('pll_get_post_types', [$this, 'lsp_add_custom_post_types'], 10, 2);
        add_filter('elementor/theme/get_location_templates/template_id', [$this, 'lsp_template_id_translation']);
        add_filter('elementor/theme/get_location_templates/condition_sub_id', [$this, 'lsp_condition_sub_id_translation'], 10, 2);
        add_filter('pre_do_shortcode_tag', [$this, 'lsp_shortcode_template_translate'], 10, 3);
        add_action('elementor/frontend/widget/before_render', [$this, 'lsp_widget_template_translate']);
        add_action('elementor/documents/register_controls', [$this, 'register_language_switcher_controls']);
        if (is_plugin_active('elementor-pro/elementor-pro.php')) {
            add_action('set_object_terms', [$this, 'lsp_update_conditions_on_term_change'], 10, 4);
        }
        
    }
    public function lsp_add_custom_post_types( $types, $is_settings ) {
        $custom_post_types = apply_filters(
            'lsp/filter/polylang/post_types',
            array(
                'elementor_library'
            )
        );

        return array_merge( $types, array_combine( $custom_post_types, $custom_post_types ) );

    }

    public function lsp_template_id_translation( $post_id ) {
        $post_id           = pll_get_post( $post_id ) ?: $post_id; //phpcs:ignore WordPress.PHP.DisallowShortTernary
        $this->template_id = $post_id; // Save for check sub_id.
        return $post_id;

    }

    public function lsp_condition_sub_id_translation( $sub_id, $parsed_condition ) {
        if ( $sub_id ) {
    
            $default_lang = pll_default_language();
            $current_lang = pll_get_post_language( $this->template_id );
    
            // Check if current template is a translation (i.e., not in default language and has a default version)
            if ( $current_lang && $current_lang !== $default_lang && pll_get_post( $this->template_id, $default_lang ) ) {
    
                if ( in_array( $parsed_condition['sub_name'], get_post_types(), true ) ) {
                    $sub_id = pll_get_post( $sub_id ) ?: $sub_id; //phpcs:ignore WordPress.PHP.DisallowShortTernary
                } else {
                    $sub_id = pll_get_term( $sub_id ) ?: $sub_id; //phpcs:ignore WordPress.PHP.DisallowShortTernary
                }
    
            }
        }
    
        return $sub_id;
    }
    

    public function lsp_shortcode_template_translate( $false, $tag, $attr ) {

        if ( 'elementor-template' !== $tag ) {
            return $false;
        }

        if ( isset( $attr['skip'] ) ) {
            return $false;
        }

        // Translate post_id.
        $attr['id'] = pll_get_post( absint( $attr['id'] ) ) ?: $attr['id']; //phpcs:ignore WordPress.PHP.DisallowShortTernary
        // Skip next call.
        $attr['skip'] = 1;

        $output = '';
        foreach ( $attr as $key => $val ) {
            $output .= " $key=\"$val\"";
        }
        return do_shortcode( '[elementor-template' . $output . ']' );

    }

    public function lsp_update_conditions_on_term_change( $post_id, $terms, $tt_ids, $taxonomy ) {
        if ( 'post_translations' === $taxonomy && 'elementor_library' === get_post_type( $post_id ) ) {

            $theme_builder = \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'theme-builder' );
            $theme_builder->get_conditions_manager()->get_cache()->regenerate();

        }

    }

    public function lsp_widget_template_translate( $element ) {

        if ( 'template' !== $element->get_name() ) {
            return;
        }

        $template_id = pll_get_post( $element->get_settings( 'template_id' ) ) ?: $element->get_settings( 'template_id' ); //phpcs:ignore WordPress.PHP.DisallowShortTernary

        $element->set_settings( 'template_id', $template_id );

    }

    public function register_language_switcher_controls( $document ) {
        // Ensure the document supports Elementor elements (PageBase types like Pages, Posts, etc.)
        // if ( ! $document instanceof \Elementor\Core\DocumentTypes\PageBase || ! $document::get_property( 'has_elements' ) ) {
        //     return;
        // }
        require_once LSP_PLUGIN_DIR . 'helpers/class-lsp-helpers.php';
        // Get the current post ID being edited in Elementor
        $post_id = $document->get_main_id();

        // Retrieve available languages from Polylang
        $languages = pll_languages_list( [ 'fields' => '' ] );

        // Get translations for the current post (returns an array of post IDs mapped by language slug)
        $translations = pll_get_post_translations( $post_id );

        // Get the current language of the post in a human-readable format
        $current_lang = pll_get_post_language( $post_id, 'name' );
        // Start adding a new section in Elementor settings panel
        $document->start_controls_section(
            'lsp_language_section',
            [
                'label' => esc_html__( 'Languages', 'language-switcher-translation-polylang-for-elementor' ),
                'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
            ]
        );
        
        // Loop through each available language
        foreach ( $languages as $language ) {
            // Check if a translation exists for the current language
            if ( isset( $translations[ $language->slug ] ) ) {
                // Get the post ID of the translated post
                $translation_id = $translations[ $language->slug ];
                // Get the standard WordPress edit link for the translated post
                $edit_link = get_edit_post_link( $translation_id, 'edit' );

                // Modify the edit link to open in Elementor editor if it's built with Elementor
                if ( get_post_meta( $translation_id, '_elementor_edit_mode', true ) ) {
                    $edit_link = add_query_arg( 'action', 'elementor', $edit_link );
                }

                // Add a control in Elementor panel with a clickable edit link for the translation
                $document->add_control(
                    "lsp_lang_{$language->slug}",
                    [
                        'type'    => \Elementor\Controls_Manager::RAW_HTML,
                        'raw'     => sprintf(
                            '<a href="%s" target="_blank"><i class="eicon-document-file"></i> %s — %s</a>',
                            esc_url( $edit_link ),
                            get_the_title( $translation_id ),
                            $language->name
                        ),
                        'content_classes' => 'elementor-control-field',
                    ]
                );
            } else {
                // If no translation exists, generate a link to create a new translation
                $args = [
                        'post_type' => get_post_type( $post_id ), // Preserve original post type
                        'from_post' => $post_id, // Reference the current post ID
                        'new_lang'  => $language->slug, // Specify the target language slug
                        '_wpnonce'  => wp_create_nonce( 'new-post-translation' ), // Security nonce
                    ];

                // Generate the create translation link
                $create_link = add_query_arg( $args, admin_url( 'post-new.php' ) );
                
                // Add a button to create a new translation
                $document->add_control(
                    "lsp_add_lang_{$language->slug}",
                    [
                        'type'    => \Elementor\Controls_Manager::RAW_HTML,
                        'raw'     => sprintf(
                            '<a href="%s" target="_blank"><i class="eicon-plus"></i> %s</a>',
                            esc_url( $create_link ),
                                sprintf( __( 'Add a translation — %s', 'language-switcher-translation-polylang-for-elementor' ),  $language->name)
                            ),
                        'content_classes' => 'elementor-control-field',
                    ]
                );
            }
        }

        // End the controls section
        $document->end_controls_section();
    }

}
new LSPManager();
