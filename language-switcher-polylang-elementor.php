<?php
/*
Plugin Name: Language Switcher & Translation – Polylang for Elementor
Plugin URI:
Description: Language Switcher & Translation – Polylang for Elementor to use language switcher in your page or Elementor header menu
Version:     1.0.0
Author:      Coolplugins
Author URI:  http://coolplugins.net/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: language-switcher-translation-polylang-for-elementor
Domain Path: /languages

Language Switcher & Translation – Polylang for Elementor is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Language Switcher & Translation – Polylang for Elementor is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Language Switcher & Translation – Polylang for Elementor. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

namespace LanguageSwitcherPolylangElementor\LSP;

define('LSP_VERSION', '1.0.0');
define('LSP_PLUGIN_NAME', 'language-switcher-translation-polylang-for-elementor');
define('LSP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LSP_PLUGIN_URL', plugin_dir_url(__FILE__));


if(! class_exists('LanguageSwitcher')){
    class LanguageSwitcher {
        public static $instance;
        public function __construct() {
            add_action( 'plugins_loaded', [ $this, 'lsp_init' ] );
            add_action('init', [ $this, 'lsp_load_textdomain']);
            add_filter( 'pll_get_post_types', array( $this, 'lsp_add_custom_post_types' ), 10, 2 );
            add_filter( 'elementor/theme/get_location_templates/template_id', array( $this, 'lsp_template_id_translation' ) );
            add_filter( 'elementor/theme/get_location_templates/condition_sub_id', array( $this, 'lsp_condition_sub_id_translation' ), 10, 2 );
            add_filter( 'pre_do_shortcode_tag', array( $this, 'lsp_shortcode_template_translate' ), 10, 3 );
            add_action( 'elementor/frontend/widget/before_render', array( $this, 'lsp_widget_template_translate' ) );
            // add_filter( 'get_post_metadata', array( $this, 'lsp_elementor_conditions_empty_on_translations' ), 10, 3 );
                add_action( 'elementor/documents/register_controls', array( $this, 'register_language_switcher_controls' ) );
            if(is_admin()){
                if(is_plugin_active('elementor-pro/elementor-pro.php')){
                    add_action( 'set_object_terms', array( $this, 'lsp_update_conditions_on_term_change' ), 10, 4 );
                }
            }
        }

        public function lsp_load_textdomain() {
            load_plugin_textdomain('language-switcher-translation-polylang-for-elementor', false, basename(dirname(__FILE__)) . '/languages');
        }

        public function lsp_init() {
            global $polylang;
            if ( ! isset( $polylang ) ) {
                add_action( 'admin_notices', array( $this, 'lsp_required_plugins_admin_notice' ) );
            }
            if (!is_plugin_active('elementor/elementor.php')){
                add_action( 'admin_notices', array( $this, 'lsp_elementor_required_admin_notice' ) );
            }

            require_once LSP_PLUGIN_DIR . 'includes/lsp-register-widget.php';
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

        // public function lsp_elementor_conditions_empty_on_translations( $null, $post_id, $meta_key ) {
        //     $default_language = pll_default_language();
        //     $post_language    = pll_get_post_language( $post_id );
        //     $original_post    = pll_get_post( $post_id, $default_language );
        
        //     $is_translation = ( $post_language !== $default_language ) && $original_post;
        //     return '_elementor_conditions' === $meta_key && $is_translation ? array( array() ) : $null;
    
        // }

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

        public function lsp_required_plugins_admin_notice() {
            if ( current_user_can( 'activate_plugins' ) ) {
                $url         = 'plugin-install.php?tab=plugin-information&plugin=polylang&TB_iframe=true';
                $title       = 'Polylang';
                $plugin_info = get_plugin_data( __FILE__, true, true );
                echo '<div class="error"><p>' .
                sprintf(
                    esc_html__(
                        'In order to use %1$s plugin, please install and activate the latest version  of %2$s',
                        'language-switcher-translation-polylang-for-elementor'
                    ),
                    wp_kses( '<strong>' . esc_html( $plugin_info['Name'] ) . '</strong>', 'strong' ),
                    wp_kses( '<a href="' . esc_url( $url ) . '" class="thickbox" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</a>', 'a' )
                ) . '.</p></div>';

                if ( function_exists( 'deactivate_plugins' ) ) {
                    deactivate_plugins( __FILE__ );
                }
            }
        }

        public function lsp_elementor_required_admin_notice() {
            if ( current_user_can( 'activate_plugins' ) ) {
                $url         = 'plugin-install.php?tab=plugin-information&plugin=elementor&TB_iframe=true';
                $title       = 'Elementor';
                $plugin_info = get_plugin_data( __FILE__, true, true );
                echo '<div class="error"><p>' .
                sprintf(
                    esc_html__(
                        'In order to use %1$s plugin, please install and activate the latest version  of %2$s',
                        'language-switcher-translation-polylang-for-elementor'
                    ),
                    wp_kses( '<strong>' . esc_html( $plugin_info['Name'] ) . '</strong>', 'strong' ),
                    wp_kses( '<a href="' . esc_url( $url ) . '" class="thickbox" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</a>', 'a' )
                ) . '.</p></div>';

                if ( function_exists( 'deactivate_plugins' ) ) {
                    deactivate_plugins( __FILE__ );
                }
            }
        }
        
        public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
    }
    LanguageSwitcher::get_instance();
}
