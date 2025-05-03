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

namespace Coolplugins\LSP;

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
            add_action('elementor/init', [ $this, 'lsp_localize_polyglang_data' ]);
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
        }
        
        public function lsp_localize_polyglang_data( $data ) {
			global $polylang;
			$lsp_polylang = $polylang;
			$data = [];
			if ( isset( $lsp_polylang ) ) {
				// if ( function_exists( 'et_fb_enabled' ) && et_fb_enabled() ) {
					try {
						require_once LSP_PLUGIN_DIR . 'helpers/class-lsp-helpers.php';
						if ( function_exists( 'pll_the_languages' ) && function_exists( 'pll_current_language' ) ) {
							$languages = pll_the_languages( array( 'raw' => 1 ) );
							if ( empty( $languages ) ) {
								return $data; // If no languages, exit early
							}
							$lang_curr = strtolower( pll_current_language() );
							$languages = array_map(
								function( $language ) {
									return $language['name'] = array(
										'flagCode'       => esc_html( \LSP_HELPERS::get_flag_code( $language['flag'] ) ),
										'slug'           => esc_html( $language['slug'] ),
										'name'           => esc_html( $language['name'] ),
										'no_translation' => esc_html( $language['no_translation'] ),
										'url'            => esc_url( $language['url'] ),
									);
								},
								$languages
							);

							$custom_data = array(
								'lspLanguageData' => $languages,
								'lspCurrentLang'   => esc_html( $lang_curr ),
								'lspPluginUrl'     => esc_url( LSP_PLUGIN_URL ),
							);
							$custom_data_json = $custom_data;

							$data['lspGlobalObj'] = $custom_data_json;
						}
					} catch ( Exception $e ) {
						// Handle exception if needed
					}
				// }
			}
			return $data;
		}

        public function lsp_required_plugins_admin_notice() {
            if ( current_user_can( 'activate_plugins' ) ) {
                $url         = 'plugin-install.php?tab=plugin-information&plugin=polylang&TB_iframe=true';
                $title       = 'Polylang';
                $plugin_info = get_plugin_data( __FILE__, true, true );
                echo '<div class="error"><p>' .
                sprintf(
                    // translators: 1: Plugin Name, 2: Plugin URL
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
                    // translators: 1: Plugin Name, 2: Plugin URL
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
