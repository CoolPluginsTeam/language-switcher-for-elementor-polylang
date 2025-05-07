<?php
if(!defined('ABSPATH')){
	exit;
}


class LSP_Register_Widget {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'lsp_register_widgets']);
        add_action('elementor/editor/before_enqueue_styles', [$this, 'lsp_enqueue_styles']);
        add_action('elementor/frontend/before_enqueue_styles', [$this, 'lsp_enqueue_styles']);
    }

    public function lsp_register_widgets() {
        require_once LSP_PLUGIN_DIR . 'includes/widget/class-lsp-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new \LanguageSwitcherPolylangElementorWidget\LSP\LSP_Widget());
    }

    public function lsp_enqueue_styles() {
        wp_enqueue_style('lsp-style', LSP_PLUGIN_URL . 'includes/css/style.css', [], LSP_VERSION);
    }
}

new LSP_Register_Widget();