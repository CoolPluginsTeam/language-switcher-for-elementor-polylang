<?php
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
    public function get_name() {
        return 'lsp_widget';
    }

    public function get_title() {
        return __('Language Switcher', 'language-switcher-translation-polylang-for-elementor');
    }

    public function get_icon() {
        return 'eicon-global-settings';
    }

    public function get_categories() {
        return ['basic'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Language Switcher', 'language-switcher-translation-polylang-for-elementor'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'lsp_language_switcher_type',
            [
                'label' => __('Language Switcher Type', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'dropdown' => __('Dropdown', 'language-switcher-translation-polylang-for-elementor'),
                    'vertical' => __('Vertical', 'language-switcher-translation-polylang-for-elementor'),
                    'horizontal' => __('Horizontal', 'language-switcher-translation-polylang-for-elementor'),
                ],
                'default' => 'dropdown',
            ]
        );

        $this->add_control(
            'lsp_language_switcher_show_flags',
            [
                'label' => __('Show Flags', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'lsp_language_switcher_show_names',
            [
                'label' => __('Show Language Names', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'lsp_languages_switcher_show_code',
            [
                'label' => __('Show Language Codes', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        
        $this->add_control(
            'lsp_language_switcher_hide_current_language',
            [
                'label' => __('Hide Current Language', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );
        
        $this->add_control(
            'lsp_language_hide_untranslated_languages',
            [
                'label' => __('Hide Untranslated Languages', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );
        
        $this->end_controls_section();
    
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Language Switcher Style', 'language-switcher-translation-polylang-for-elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'lsp_language_switcher_alignment',
            [
                'label' => __('Switcher Alignment', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'language-switcher-translation-polylang-for-elementor' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'language-switcher-translation-polylang-for-elementor' ),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'language-switcher-translation-polylang-for-elementor' ),
						'icon' => 'eicon-h-align-right',
					],
                    'stretch' => [
                        'title' => esc_html__( 'Stretch', 'language-switcher-translation-polylang-for-elementor' ),
                        'icon' => 'eicon-h-align-stretch',
                    ],
				],
				'default' => 'left',
            ]
        );

        $this->add_control(
            'lsp_language_swtcher_flag_ratio',
            [
                'label' => __('Flag Ratio', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1:1' => __('1:1', 'language-switcher-translation-polylang-for-elementor'),
                    '4:3' => __('4:3', 'language-switcher-translation-polylang-for-elementor'),
                ],
                'default' => '4:3',
            ]
        );

        $this->add_control(
            'lsp_language_switcher_flag_radius',
            [
                'label' => __('Flag Radius', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%  ' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 0,
                ],
            ]
        );

        $this->add_control(
            'lsp_language_switcher_style',
            [
                'label' => __('Switcher Style', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'light' => __('Light', 'language-switcher-translation-polylang-for-elementor'),
                    'dark' => __('Dark', 'language-switcher-translation-polylang-for-elementor'),
                ],
                'default' => 'light',

            ]
        );

        $this->add_control(
            'lsp_language_switcher_dropown_direction',
            [
                'label' => __('Dropdown Direction', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'up' => __('Up', 'language-switcher-translation-polylang-for-elementor'),
                    'down' => __('Down', 'language-switcher-translation-polylang-for-elementor'),
                ],
                'default' => 'down',
            ]
        );

        $this->add_control(
			'lsp_language_switcher_margin',
			[
				'label' => esc_html__( 'Margin', 'language-switcher-translation-polylang-for-elementor' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default' => [
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
				],
			]
		);

        $this->add_control(
            'lsp_language_switcher_padding',
            [
                'label' => __('Padding', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                ],
            ]
        );
        
        $this->start_controls_tabs('lsp_language_switcher_style_tabs');
        $this->start_controls_tab(
            'lsp_language_switcher_style_tab_normal',
            [
                'label' => __('Normal', 'language-switcher-translation-polylang-for-elementor'),
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'lsp_language_switcher_typography',
                'label' => __('Typography', 'language-switcher-translation-polylang-for-elementor'),
            ]
        );
        $this->add_control(
            'lsp_language_switcher_background_color',
            [
                'label' => __('Switcher Background Color', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::COLOR,
            ]
        );

        $this->add_control(
            'lsp_language_switcher_text_color',
            [
                'label' => __('Switcher Text Color', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::COLOR,
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'lsp_language_switcher_style_tab_hover',
            [
                'label' => __('Hover', 'language-switcher-translation-polylang-for-elementor'),
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'lsp_language_switcher_typography_hover',
                'label' => __('Typography', 'language-switcher-translation-polylang-for-elementor'),
            ]
        );
        $this->add_control(
            'lsp_language_switcher_background_color_hover',
            [
                'label' => __('Switcher Background Color', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::COLOR,
            ]
        );

        $this->add_control(
            'lsp_language_switcher_text_color_hover',
            [
                'label' => __('Switcher Text Color', 'language-switcher-translation-polylang-for-elementor'),
                'type' => Controls_Manager::COLOR,
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();
        
        $this->end_controls_section();

    }
    
    protected function render() {
        echo 'Hello World';
    }
}

// Register the widget
\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new LSP_Widget());