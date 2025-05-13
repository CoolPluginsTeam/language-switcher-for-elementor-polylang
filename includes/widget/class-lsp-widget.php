<?php

namespace LanguageSwitcherPolylangElementorWidget\LSP;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Typography;
use Elementor\Scheme_Color;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Widget_Base;

if(!defined('ABSPATH')){
	exit;
}


class LSP_Widget extends Widget_Base {

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        wp_register_style('lsp-style', LSP_PLUGIN_URL . '/includes/css/language-switcher-style.css', [], LSP_VERSION);
    }

    public function get_name() {
        return 'lsp_widget';
    }

    public function get_title() {
        return __('Language Switcher', 'language-switcher-polylang-elementor');
    }

    public function get_icon() {
        return 'eicon-global-settings';
    }

    public function get_categories() {
        return ['basic'];
    }

    public function get_style_depends() {
        return ['lsp-style'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Language Switcher', 'language-switcher-polylang-elementor'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'lsp_language_switcher_type',
            [
                'label' => __('Language Switcher Type', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'dropdown' => __('Dropdown', 'language-switcher-polylang-elementor'),
                    'vertical' => __('Vertical', 'language-switcher-polylang-elementor'),
                    'horizontal' => __('Horizontal', 'language-switcher-polylang-elementor'),
                ],
                'default' => 'dropdown',
            ]
        );

        $this->add_control(
            'lsp_language_switcher_show_flags',
            [
                'label' => __('Show Flags', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'lsp_language_switcher_show_names',
            [
                'label' => __('Show Language Names', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'lsp_languages_switcher_show_code',
            [
                'label' => __('Show Language Codes', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        
        $this->add_control(
            'lsp_language_switcher_hide_current_language',
            [
                'label' => __('Hide Current Language', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );
        
        $this->add_control(
            'lsp_language_hide_untranslated_languages',
            [
                'label' => __('Hide Untranslated Languages', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );
        
        $this->end_controls_section();
    
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Language Switcher Style', 'language-switcher-polylang-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'lsp_language_switcher_alignment',
            [
                'label' => __('Switcher Alignment', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'language-switcher-polylang-elementor' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'language-switcher-polylang-elementor' ),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'language-switcher-polylang-elementor' ),
						'icon' => 'eicon-h-align-right',
					]
				],
				'default' => 'left',
                'condition' => [
                    'lsp_language_switcher_type' => 'dropdown',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsp-main-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'lsp_language_swtcher_flag_ratio',
            [
                'label' => __('Flag Ratio', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '11' => __('1/1', 'language-switcher-polylang-elementor'),
                    '43' => __('4/3', 'language-switcher-polylang-elementor'),
                ],
                'prefix_class' => 'lsp-switcher--aspect-ratio-',
                'default' => '43',
                'selectors' => [
                    '{{WRAPPER}} .lsp-lang-image' => '--lsp-flag-ratio: {{VALUE}};',
                ],
                'condition' => [
                    'lsp_language_switcher_show_flags' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'lsp_language_switcher_flag_width',
            [
                'label' => __('Flag Width', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}}.lsp-switcher--aspect-ratio-11 .lsp-lang-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.lsp-switcher--aspect-ratio-43 .lsp-lang-image img' => 'width: {{SIZE}}{{UNIT}}; height: calc({{SIZE}}{{UNIT}} * 0.75);'
                ],
                'condition' => [
                    'lsp_language_switcher_show_flags' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'lsp_language_switcher_flag_radius',
            [
                'label' => __('Flag Radius', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsp-lang-image img' => '--lsp-flag-radius: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'lsp_language_switcher_show_flags' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
			'lsp_language_switcher_margin',
			[
				'label' => esc_html__( 'Margin', 'language-switcher-polylang-elementor' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default' => [
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
				],
                'selectors' => [
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    // '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.horizontal .lsp-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.vertical .lsp-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
			]
		);

        $this->add_control(
            'lsp_language_switcher_padding',
            [
                'label' => __('Padding', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'default' => [
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 10,
                    'left' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-lang-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.horizontal .lsp-lang-item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.vertical .lsp-lang-item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->start_controls_tabs('lsp_language_switcher_style_tabs');
        $this->start_controls_tab(
            'lsp_language_switcher_style_tab_normal',
            [
                'label' => __('Normal', 'language-switcher-polylang-elementor'),
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'lsp_language_switcher_typography',
                'label' => __('Typography', 'language-switcher-polylang-elementor'),
                'selector' => '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-active-language,{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-lang-item a, {{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.horizontal .lsp-lang-item a, {{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.vertical .lsp-lang-item a',
            ]
        );
        $this->add_control(
            'lsp_language_switcher_background_color',
            [
                'label' => __('Switcher Background Color', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown' => '--lsp-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown ul li' => '--lsp-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.horizontal .lsp-lang-item a' => '--lsp-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.vertical .lsp-lang-item a' => '--lsp-normal-bg-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'lsp_language_switcher_text_color',
            [
                'label' => __('Switcher Text Color', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-active-language,{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-lang-item a, {{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.horizontal .lsp-lang-item a, {{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.vertical .lsp-lang-item a' => '--lsp-normal-text-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'lsp_language_switcher_style_tab_hover',
            [
                'label' => __('Hover', 'language-switcher-polylang-elementor'),
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'lsp_language_switcher_typography_hover',
                'label' => __('Typography', 'language-switcher-polylang-elementor'),
                'selector' => '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-active-language:hover,{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-lang-item a:hover, {{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.horizontal .lsp-lang-item a:hover, {{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.vertical .lsp-lang-item a:hover',
            ]
        );
        $this->add_control(
            'lsp_language_switcher_background_color_hover',
            [
                'label' => __('Switcher Background Color', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown:hover' => '--lsp-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown ul li:hover' => '--lsp-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.horizontal .lsp-lang-item a:hover' => '--lsp-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.vertical .lsp-lang-item a:hover' => '--lsp-normal-bg-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'lsp_language_switcher_text_color_hover',
            [
                'label' => __('Switcher Text Color', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown:hover .lsp-active-language,{{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.dropdown .lsp-lang-item:hover a, {{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.horizontal .lsp-lang-item a:hover, {{WRAPPER}} .lsp-main-wrapper .lsp-wrapper.vertical .lsp-lang-item a:hover' => '--lsp-normal-text-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();
        
        $this->end_controls_section();

        $this->start_controls_section(
            'section_dropdown_style',
            [
                'label' => __('Dropdown Style', 'language-switcher-polylang-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'lsp_language_switcher_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'lsp_language_switcher_dropown_direction',
            [
                'label' => __('Dropdown Direction', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'up' => __('Up', 'language-switcher-polylang-elementor'),
                    'down' => __('Down', 'language-switcher-polylang-elementor'),
                ],
                'default' => 'down',
                'condition' => [
                    'lsp_language_switcher_type' => 'dropdown',
                ],
                'prefix_class' => 'lsp-dropdown-direction-',
            ]
        );

        $this->add_control(
            'lsp_language_switcher_icon',
            [
                'label' => __('Switcher Icon', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value'   => 'fas fa-caret-down',
                    'library' => 'fa-solid',
                ],
                'include' => [ 'fa-solid', 'fa-regular', 'fa-brands' ],
                'exclude_inline_options' => 'svg',
                'label_block' => false,
                'skin' => 'inline',
                'condition' => [
                    'lsp_language_switcher_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'lsp_language_switcher_icon_size',
            [
                'label' => __('Icon Size', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'lsp_language_switcher_type' => 'dropdown',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsp-dropdown-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'lsp_language_switcher_icon_color',
            [
                'label' => __('Icon Color', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'lsp_language_switcher_type' => 'dropdown',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsp-dropdown-icon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'lsp_language_switcher_icon_spacing',
            [
                'label' => __('Icon Spacing', 'language-switcher-polylang-elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'lsp_language_switcher_type' => 'dropdown',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsp-dropdown-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
        
    }
    
    public function lsp_localize_polyglang_data( $data ) {
        global $polylang;
        $lsp_polylang = $polylang;
        $data = [];
        if ( isset( $lsp_polylang ) ) {
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
                                    'flag'           => esc_html( $language['flag'] ),
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
        }
        return $data;
    }

    protected function render() {
        $settings = $this->get_active_settings();
        
        // Get the localized data
        $data = $this->lsp_localize_polyglang_data(array());
        $lsp_data = isset($data['lspGlobalObj']) ? $data['lspGlobalObj'] : array();
        if(empty($lsp_data)){
            return;
        }
        if($settings['lsp_language_switcher_show_flags'] !== 'yes' && $settings['lsp_language_switcher_show_names'] !== 'yes' && $settings['lsp_languages_switcher_show_code'] !== 'yes'){
            return;
        }
        $switcher_html = '';
        $switcher_html .= '<div class="lsp-main-wrapper">';
        if($settings['lsp_language_switcher_type'] == 'dropdown'){
            $switcher_html .= '<div class="lsp-wrapper dropdown">';
            $switcher_html .= $this->lsp_render_dropdown_switcher($settings, $lsp_data);
            $switcher_html .= '</div>';
        }else{
            $switcher_html .= '<div class="lsp-wrapper ' . $settings['lsp_language_switcher_type'] . '">';
            $switcher_html .= $this->lsp_render_switcher($settings, $lsp_data);
            $switcher_html .= '</div>';
        }
        $switcher_html .= '</div>';
        echo ( $switcher_html );
    }

    public function lsp_render_dropdown_switcher($settings, $lsp_data){
        $languages = $lsp_data['lspLanguageData'];
        $current_lang = $lsp_data['lspCurrentLang'];
        
        // If current language should be shown, use it as active language
        if ($settings['lsp_language_switcher_hide_current_language'] !== 'yes') {
            $active_language = $languages[$current_lang];
        } else {
            // Find first available language that's not the current language
            $active_language = null;
            foreach ($languages as $lang) {
                if ($current_lang !== $lang['slug'] && 
                    !($lang['no_translation'] && $settings['lsp_language_hide_untranslated_languages'] === 'yes')) {
                    $active_language = $lang;
                    break;
                }
            }
        }
        
        // If no language found, return empty
        if (!$active_language) {
            return '';
        }
        
        $active_html = self::get_active_language_html($active_language, $settings);
        $languages_html = '';
        
        foreach ($languages as $lang) {
            // Skip if it's the current language (when hidden), active language, or untranslated language
            if (($current_lang === $lang['slug'] && $settings['lsp_language_switcher_hide_current_language'] === 'yes') || 
                $active_language['slug'] === $lang['slug'] || 
                ($lang['no_translation'] && $settings['lsp_language_hide_untranslated_languages'] === 'yes')) {
                continue;
            }

            $flag_icon = \LSP_HELPERS::get_country_flag($lang['flag'], $lang['name']);

            $languages_html .= '<li class="lsp-lang-item">';
            $languages_html .= '<a href="' . esc_url($lang['url']) . '">';
            if (!empty($settings['lsp_language_switcher_show_flags']) && $settings['lsp_language_switcher_show_flags'] === 'yes') {
                $languages_html .= '<div class="lsp-lang-image">' . $flag_icon . '</div>';
            }
            if (!empty($settings['lsp_language_switcher_show_names']) && $settings['lsp_language_switcher_show_names'] === 'yes') {
                $languages_html .= '<div class="lsp-lang-name">' . esc_html($lang['name']) . '</div>';
            }
            if (!empty($settings['lsp_languages_switcher_show_code']) && $settings['lsp_languages_switcher_show_code'] === 'yes') {
                $languages_html .= '<div class="lsp-lang-code">' . esc_html($lang['slug']) . '</div>';
            }
            $languages_html .= '</a></li>';
        }

        return $active_html . '<ul class="lsp-language-list">' . $languages_html . '</ul>';
    }

    public static function get_active_language_html($language, $settings){
        $html = '<span class="lsp-active-language">';
        $html .= '<a href="' . esc_url($language['url']) . '">';
        if (!empty($settings['lsp_language_switcher_show_flags']) && $settings['lsp_language_switcher_show_flags'] === 'yes') {
            $html .= '<div class="lsp-lang-image">' . \LSP_HELPERS::get_country_flag($language['flag'], $language['name']) . '</div>';
        }
        if (!empty($settings['lsp_language_switcher_show_names']) && $settings['lsp_language_switcher_show_names'] === 'yes') {
            $html .= '<div class="lsp-lang-name">' . esc_html($language['name']) . '</div>';
        }
        if (!empty($settings['lsp_languages_switcher_show_code']) && $settings['lsp_languages_switcher_show_code'] === 'yes') {
            $html .= '<div class="lsp-lang-code">' . esc_html($language['slug']) . '</div>';
        }
        if(!empty($settings['lsp_language_switcher_icon'])){
            $html .= '<i class="lsp-dropdown-icon ' . $settings['lsp_language_switcher_icon']['value'] . '"></i>';
        }
        $html .= '</a></span>';
        return $html;
    }

    public static function lsp_render_switcher($settings, $lsp_data){
        $html = '';
        $languages = $lsp_data['lspLanguageData'];
        $current_lang = $lsp_data['lspCurrentLang'];
        foreach ($languages as $lang) {
            if (($current_lang === $lang['slug'] && $settings['lsp_language_switcher_hide_current_language'] === 'yes') ||
                ($lang['no_translation'] && $settings['lsp_language_hide_untranslated_languages'] === 'yes')) {
                continue;
            }

            $flag_icon = \LSP_HELPERS::get_country_flag($lang['flag'], $lang['name']);
            $anchor_open = '<a href="' . esc_url($lang['url']) . '">';
            $anchor_close = '</a>';

            $html .= '<li class="lsp-lang-item">';
            $html .= $anchor_open;
            if (!empty($settings['lsp_language_switcher_show_flags']) && $settings['lsp_language_switcher_show_flags'] === 'yes') {
                $html .= '<div class="lsp-lang-image">' .$flag_icon .'</div>';
            }
            if (!empty($settings['lsp_language_switcher_show_names']) && $settings['lsp_language_switcher_show_names'] === 'yes') {
                $html .= '<div class="lsp-lang-name">' . wp_kses_post(esc_html($lang['name'])) . '</div>';
            }
            if (!empty($settings['lsp_languages_switcher_show_code']) && $settings['lsp_languages_switcher_show_code'] === 'yes') {
                $html .= '<div class="lsp-lang-code">' . wp_kses_post(esc_html($lang['slug'])) . '</div>';
            }
            $html .= $anchor_close;
            $html .= '</li>';
        }
        return $html;
    }
}