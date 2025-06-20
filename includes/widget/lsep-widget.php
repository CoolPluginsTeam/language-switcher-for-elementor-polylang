<?php
/**
 * Language Switcher Polylang Elementor Widget
 *
 * @package LanguageSwitcherPolylangElementorWidget
 * @since 1.0.0
 */

namespace LSEP\LanguageSwitcherPolylangElementorWidget;
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

/**
 * Class LSEP_Widget
 *
 * Main widget class for the Language Switcher Polylang Elementor widget.
 *
 * @since 1.0.0
 */
class LSEP_Widget extends Widget_Base {

	/**
	 * Constructor for the widget.
	 *
	 * @param array $data Widget data.
	 * @param array $args Widget arguments.
	 */
   public function __construct($data = [], $args = null) {
    parent::__construct($data, $args);

    wp_register_style(
        'lsep-style',
        LSEP_PLUGIN_URL . '/includes/css/language-switcher-style.css',
        [],
        LSEP_VERSION
    );

    add_action('elementor/editor/after_enqueue_scripts', array($this, 'lsep_language_switcher_icon_css'));
}

public function lsep_language_switcher_icon_css() {
    wp_enqueue_style('lsep-style');

    $inline_css = "
        .lsep-widget-icon {
            display: inline-block;
            width: 25px;
            height: 25px;
            background-image: url('" . esc_url(LSEP_PLUGIN_URL . '/assets/images/lang_switcher.svg') . "');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
    ";

    wp_add_inline_style('lsep-style', $inline_css);
}

    

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
    public function get_name() {
        return 'lsep_widget';
    }

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
    public function get_title() {
        return __('Language Switcher', 'language-switcher-for-elementor-polylang');
    }

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
    public function get_icon() {
       return 'lsep-widget-icon';
    }

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
    public function get_categories() {
        return ['basic'];
    }

	/**
	 * Get widget style dependencies.
	 *
	 * @return array Widget style dependencies.
	 */
    public function get_style_depends() {
        return ['lsep-style'];
    }

	/**
	 * Register widget controls.
	 */
    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Language Switcher', 'language-switcher-for-elementor-polylang'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'lsep_language_switcher_type',
            [
                'label' => __('Language Switcher Type', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'dropdown' => __('Dropdown', 'language-switcher-for-elementor-polylang'),
                    'vertical' => __('Vertical', 'language-switcher-for-elementor-polylang'),
                    'horizontal' => __('Horizontal', 'language-switcher-for-elementor-polylang'),
                ],
                'default' => 'dropdown',
            ]
        );

        $this->add_control(
            'lsep_language_switcher_show_flags',
            [
                'label' => __('Show Flags', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'lsep_language_switcher_show_names',
            [
                'label' => __('Show Language Names', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'lsep_languages_switcher_show_code',
            [
                'label' => __('Show Language Codes', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        
        $this->add_control(
            'lsep_language_switcher_hide_current_language',
            [
                'label' => __('Hide Current Language', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );
        
        $this->add_control(
            'lsep_language_hide_untranslated_languages',
            [
                'label' => __('Hide Untranslated Languages', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );
        
        if ( ! get_option( 'lsep_elementor_review_notice_dismiss' ) ) {
            $review_nonce = wp_create_nonce( 'lsep_elementor_review' );
            $url          = admin_url( 'admin-ajax.php' );
            $html         = '<div class="lsep_elementor_review_wrapper">';
            $html        .= '<div id="lsep_elementor_review_dismiss" data-url="' . esc_url( $url ) . '" data-nonce="' . esc_attr( $review_nonce ) . '">Close Notice X</div>
                            <div class="lsep_elementor_review_msg">' . __( 'Hope this language switcher solved your problem!', 'language-switcher-for-elementor-polylang' ) . '<br><a href="https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/reviews/#new-post" target="_blank">Share the love with a ⭐⭐⭐⭐⭐ rating.</a><br><br></div>
                            <div class="lsep_elementor_demo_btn"><a href="https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/reviews/#new-post" target="_blank">Submit Review</a></div>
                            </div>';

            $this->add_control(
                'lsep_review_notice',
                [
                    'name'            => 'lsep_review_notice',
                    'type'            => Controls_Manager::RAW_HTML,
                    'raw'             => $html,
                    'content_classes' => 'lsep_elementor_review_notice',
                ]
            );
        }

        $this->end_controls_section();
    
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Language Switcher Style', 'language-switcher-for-elementor-polylang'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'lsep_language_switcher_alignment',
            [
                'label' => __('Switcher Alignment', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'language-switcher-for-elementor-polylang' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'language-switcher-for-elementor-polylang' ),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'language-switcher-for-elementor-polylang' ),
						'icon' => 'eicon-h-align-right',
					]
				],
				'default' => 'left',
                'condition' => [
                    'lsep_language_switcher_type' => 'dropdown',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsep-main-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_swtcher_flag_ratio',
            [
                'label' => __('Flag Ratio', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '11' => __('1/1', 'language-switcher-for-elementor-polylang'),
                    '43' => __('4/3', 'language-switcher-for-elementor-polylang'),
                ],
                'prefix_class' => 'lsep-switcher--aspect-ratio-',
                'default' => '43',
                'selectors' => [
                    '{{WRAPPER}} .lsep-lang-image' => '--lsep-flag-ratio: {{VALUE}};',
                ],
                'condition' => [
                    'lsep_language_switcher_show_flags' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_flag_width',
            [
                'label' => __('Flag Width', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}}.lsep-switcher--aspect-ratio-11 .lsep-lang-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.lsep-switcher--aspect-ratio-43 .lsep-lang-image img' => 'width: {{SIZE}}{{UNIT}}; height: calc({{SIZE}}{{UNIT}} * 0.75);'
                ],
                'condition' => [
                    'lsep_language_switcher_show_flags' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'lsep_language_switcher_flag_radius',
            [
                'label' => __('Flag Radius', 'language-switcher-for-elementor-polylang'),
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
                    '{{WRAPPER}} .lsep-lang-image img' => '--lsep-flag-radius: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'lsep_language_switcher_show_flags' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
			'lsep_language_switcher_margin',
			[
				'label' => esc_html__( 'Margin', 'language-switcher-for-elementor-polylang' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'default' => [
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
				],
                'selectors' => [
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    // '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
			]
		);

        $this->add_control(
            'lsep_language_switcher_padding',
            [
                'label' => __('Padding', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'default' => [
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 10,
                    'left' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'lsep_language_switcher_border',
				'label' => __('Border', 'language-switcher-for-elementor-polylang'),
				'selector' => '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal li a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical li a',
			]
		);

        $this->add_control(
            'lsep_language_switcher_border_radius',
            [
                'label' => __('Border Radius', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-language-list' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],

            ]
        );
        $this->start_controls_tabs('lsep_language_switcher_style_tabs');
        $this->start_controls_tab(
            'lsep_language_switcher_style_tab_normal',
            [
                'label' => __('Normal', 'language-switcher-for-elementor-polylang'),
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'lsep_language_switcher_typography',
                'label' => __('Typography', 'language-switcher-for-elementor-polylang'),
                'selector' => '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-active-language a div:not(.lsep-lang-image), {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a',
            ]
        );
        $this->add_control(
            'lsep_language_switcher_background_color',
            [
                'label' => __('Switcher Background Color', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown' => '--lsep-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown ul li' => '--lsep-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a' => '--lsep-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a' => '--lsep-normal-bg-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_text_color',
            [
                'label' => __('Switcher Text Color', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-active-language,{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a' => '--lsep-normal-text-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'lsep_language_switcher_style_tab_hover',
            [
                'label' => __('Hover', 'language-switcher-for-elementor-polylang'),
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'lsep_language_switcher_typography_hover',
                'label' => __('Typography', 'language-switcher-for-elementor-polylang'),
                'selector' => '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-active-language:hover,{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item a:hover, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a:hover, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a:hover',
            ]
        );
        $this->add_control(
            'lsep_language_switcher_background_color_hover',
            [
                'label' => __('Switcher Background Color', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown:hover' => '--lsep-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown ul li:hover' => '--lsep-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a:hover' => '--lsep-normal-bg-color: {{VALUE}};',
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a:hover' => '--lsep-normal-bg-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_text_color_hover',
            [
                'label' => __('Switcher Text Color', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown:hover .lsep-active-language,{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown .lsep-lang-item:hover a, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.horizontal .lsep-lang-item a:hover, {{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.vertical .lsep-lang-item a:hover' => '--lsep-normal-text-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();
        
        $this->end_controls_section();

        $this->start_controls_section(
            'section_dropdown_style',
            [
                'label' => __('Dropdown Style', 'language-switcher-for-elementor-polylang'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'lsep_language_switcher_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_dropown_direction',
            [
                'label' => __('Dropdown Direction', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'up' => __('Up', 'language-switcher-for-elementor-polylang'),
                    'down' => __('Down', 'language-switcher-for-elementor-polylang'),
                ],
                'default' => 'down',
                'condition' => [
                    'lsep_language_switcher_type' => 'dropdown',
                ],
                'prefix_class' => 'lsep-dropdown-direction-',
            ]
        );

        $this->add_control(
            'lsep_language_switcher_icon',
            [
                'label' => __('Switcher Icon', 'language-switcher-for-elementor-polylang'),
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
                    'lsep_language_switcher_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_icon_size',
            [
                'label' => __('Icon Size', 'language-switcher-for-elementor-polylang'),
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
                    'lsep_language_switcher_type' => 'dropdown',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsep-dropdown-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_icon_color',
            [
                'label' => __('Icon Color', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'lsep_language_switcher_type' => 'dropdown',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsep-dropdown-icon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_icon_spacing',
            [
                'label' => __('Icon Spacing', 'language-switcher-for-elementor-polylang'),
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
                    'lsep_language_switcher_type' => 'dropdown',
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsep-dropdown-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_dropdwon_spacing',
            [
                'label' => __('Dropdown Spacing', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}}.lsep-dropdown-direction-down .lsep-wrapper.dropdown ul' => 'margin-top: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.lsep-dropdown-direction-up .lsep-wrapper.dropdown ul' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'lsep_language_switcher_dropdown_list_border',
                'label' => __('Dropdown List Border', 'language-switcher-for-elementor-polylang'),
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .lsep-main-wrapper .lsep-wrapper.dropdown ul',
                'fields_options' => [
                    'border' => [
                        'label' => __('Dropdown List Border', 'language-switcher-for-elementor-polylang'),
                    ],
                    'width' => [
                        'label' => __('Border Width', 'language-switcher-for-elementor-polylang'),
                    ],
                    'color' => [
                        'label' => __('Border Color', 'language-switcher-for-elementor-polylang'),
                    ],
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_dropdown_language_item_separator',
            [
                'label' => __('Language Item Separator', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .lsep-wrapper.dropdown ul.lsep-language-list li.lsep-lang-item:not(:last-child)' => 'border-bottom: {{SIZE}}{{UNIT}} solid;',
                ],
            ]
        );

        $this->add_control(
            'lsep_language_switcher_dropdown_language_item_separator_color',
            [
                'label' => __('Separator Color', 'language-switcher-for-elementor-polylang'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .lsep-wrapper.dropdown ul.lsep-language-list li.lsep-lang-item:not(:last-child)' => 'border-bottom-color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_section();
    }
    
	/**
	 * Localize Polylang data for the widget.
	 *
	 * @param array $data Data to be localized.
	 * @return array Localized data.
	 */
    public function lsep_localize_polyglang_data( $data ) {
        // Get the global Polylang object
        global $polylang;
        $lsep_polylang = $polylang;
        $data = [];
        if ( isset( $lsep_polylang ) ) {
                try {
                    require_once LSEP_PLUGIN_DIR . 'helpers/lsep-helpers.php';
                    if ( function_exists( 'pll_the_languages' ) && function_exists( 'pll_current_language' ) ) {
                        $languages = pll_the_languages( array( 'raw' => 1 ) );
                        if ( empty( $languages ) ) {
                            return $data; // If no languages, exit early
                        }
                        $lang_curr = strtolower( pll_current_language() );
                        $languages = array_map(
                            function( $language ) {
                                return $language['name'] = array(
                                    'flagCode'       => esc_html( \LSEP_HELPERS::lsep_get_flag_code( $language['flag'] ) ),
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
                            'lsepLanguageData' => $languages,
                            'lsepCurrentLang'   => esc_html( $lang_curr ),
                            'lsepPluginUrl'     => esc_url( LSEP_PLUGIN_URL ),
                        );
                        $custom_data_json = $custom_data;

                        $data['lsepGlobalObj'] = $custom_data_json;
                    }
                } catch ( Exception $e ) {
                    // Handle exception if needed
                }
        }
        return $data;
    }

	/**
	 * Render the widget output on the frontend.
	 */
    protected function render() {
        $settings = $this->get_active_settings();
        
        // Get the localized data
        $data = $this->lsep_localize_polyglang_data(array());
        $lsep_data = isset($data['lsepGlobalObj']) ? $data['lsepGlobalObj'] : array();
        if(empty($lsep_data)){
            return;
        }
        if($settings['lsep_language_switcher_show_flags'] !== 'yes' && $settings['lsep_language_switcher_show_names'] !== 'yes' && $settings['lsep_languages_switcher_show_code'] !== 'yes'){
            return;
        }
        $switcher_html = '';
        $switcher_html .= '<div class="lsep-main-wrapper">';
        if($settings['lsep_language_switcher_type'] == 'dropdown'){
            $switcher_html .= '<div class="lsep-wrapper dropdown">';
            $switcher_html .= $this->lsep_render_dropdown_switcher($settings, $lsep_data);
            $switcher_html .= '</div>';
        }else{
            $switcher_html .= '<div class="lsep-wrapper ' . esc_attr($settings['lsep_language_switcher_type']) . '">';
            $switcher_html .= $this->lsep_render_switcher($settings, $lsep_data);
            $switcher_html .= '</div>';
        }
        $switcher_html .= '</div>';
        echo wp_kses_post($switcher_html);
    }

	/**
	 * Render dropdown switcher.
	 *
	 * @param array $settings Widget settings.
	 * @param array $lsep_data Language data.
	 * @return string HTML output.
	 */
    public function lsep_render_dropdown_switcher($settings, $lsep_data){
        $languages = $lsep_data['lsepLanguageData'];
        $current_lang = $lsep_data['lsepCurrentLang'];
        
        // If current language should be shown, use it as active language
        if ($settings['lsep_language_switcher_hide_current_language'] !== 'yes') {
            $active_language = $languages[$current_lang];
        } else {
            // Find first available language that's not the current language
            $active_language = null;
            foreach ($languages as $lang) {
                if ($current_lang !== $lang['slug'] && 
                    !($lang['no_translation'] && $settings['lsep_language_hide_untranslated_languages'] === 'yes')) {
                    $active_language = $lang;
                    break;
                }
            }
        }
        
        // If no language found, return empty
        if (!$active_language) {
            return '';
        }
        
        $active_html = self::lsep_get_active_language_html($active_language, $settings);
        $languages_html = '';
        
        foreach ($languages as $lang) {
            // Skip if it's the current language (when hidden), active language, or untranslated language
            if (($current_lang === $lang['slug'] && $settings['lsep_language_switcher_hide_current_language'] === 'yes') || 
                $active_language['slug'] === $lang['slug'] || 
                ($lang['no_translation'] && $settings['lsep_language_hide_untranslated_languages'] === 'yes')) {
                continue;
            }

            $flag_icon = \LSEP_HELPERS::lsep_get_country_flag($lang['flag'], $lang['name']);

            $languages_html .= '<li class="lsep-lang-item">';
            $languages_html .= '<a href="' . esc_url($lang['url']) . '">';
            if (!empty($settings['lsep_language_switcher_show_flags']) && $settings['lsep_language_switcher_show_flags'] === 'yes') {
                $languages_html .= '<div class="lsep-lang-image">' . $flag_icon . '</div>';
            }
            if (!empty($settings['lsep_language_switcher_show_names']) && $settings['lsep_language_switcher_show_names'] === 'yes') {
                $languages_html .= '<div class="lsep-lang-name">' . esc_html($lang['name']) . '</div>';
            }
            if (!empty($settings['lsep_languages_switcher_show_code']) && $settings['lsep_languages_switcher_show_code'] === 'yes') {
                $languages_html .= '<div class="lsep-lang-code">' . esc_html($lang['slug']) . '</div>';
            }
            $languages_html .= '</a></li>';
        }

        return $active_html . '<ul class="lsep-language-list">' . $languages_html . '</ul>';
    }

	/**
	 * Get active language HTML.
	 *
	 * @param array  $language Language data.
	 * @param array  $settings Widget settings.
	 * @return string HTML output.
	 */
    public static function lsep_get_active_language_html($language, $settings){
        $html = '<span class="lsep-active-language">';
        $html .= '<a href="' . esc_url($language['url']) . '">';
        $flag_icon = \LSEP_HELPERS::lsep_get_country_flag($language['flag'], $language['name']);
        if (!empty($settings['lsep_language_switcher_show_flags']) && $settings['lsep_language_switcher_show_flags'] === 'yes') {
            $html .= '<div class="lsep-lang-image">' . $flag_icon . '</div>';
        }
        if (!empty($settings['lsep_language_switcher_show_names']) && $settings['lsep_language_switcher_show_names'] === 'yes') {
            $html .= '<div class="lsep-lang-name">' . esc_html($language['name']) . '</div>';
        }
        if (!empty($settings['lsep_languages_switcher_show_code']) && $settings['lsep_languages_switcher_show_code'] === 'yes') {
            $html .= '<div class="lsep-lang-code">' . esc_html($language['slug']) . '</div>';
        }
        if(!empty($settings['lsep_language_switcher_icon'])){
            $html .= '<i class="lsep-dropdown-icon ' . esc_attr($settings['lsep_language_switcher_icon']['value']) . '"></i>';
        }
        $html .= '</a></span>';
        return $html;
    }

	/**
	 * Render Vertcal and Horizontal switcher.
	 *
	 * @param array $settings Widget settings.
	 * @param array $lsep_data Language data.
	 * @return string HTML output.
	 */
    public static function lsep_render_switcher($settings, $lsep_data){
        $html = '';
        $languages = $lsep_data['lsepLanguageData'];
        $current_lang = $lsep_data['lsepCurrentLang'];
        foreach ($languages as $lang) {
            if (($current_lang === $lang['slug'] && $settings['lsep_language_switcher_hide_current_language'] === 'yes') ||
                ($lang['no_translation'] && $settings['lsep_language_hide_untranslated_languages'] === 'yes')) {
                continue;
            }

            $flag_icon = \LSEP_HELPERS::lsep_get_country_flag($lang['flag'], $lang['name']);
            $anchor_open = '<a href="' . esc_url($lang['url']) . '">';
            $anchor_close = '</a>';

            $html .= '<li class="lsep-lang-item">';
            $html .= $anchor_open;
            if (!empty($settings['lsep_language_switcher_show_flags']) && $settings['lsep_language_switcher_show_flags'] === 'yes') {
                $html .= '<div class="lsep-lang-image">' . ($flag_icon) . '</div>';
            }
            if (!empty($settings['lsep_language_switcher_show_names']) && $settings['lsep_language_switcher_show_names'] === 'yes') {
                $html .= '<div class="lsep-lang-name">' . esc_html($lang['name']) . '</div>';
            }
            if (!empty($settings['lsep_languages_switcher_show_code']) && $settings['lsep_languages_switcher_show_code'] === 'yes') {
                $html .= '<div class="lsep-lang-code">' . esc_html($lang['slug']) . '</div>';
            }
            $html .= $anchor_close;
            $html .= '</li>';
        }
        return $html;
    }
}