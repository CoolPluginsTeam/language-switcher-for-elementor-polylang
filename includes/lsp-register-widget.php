<?php
if(!defined('ABSPATH')){
	exit;
}


class LSP_Register_Widget {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'lsp_register_widgets']);
        add_action('elementor/frontend/after_register_styles', [$this, 'lsp_register_styles']);
        
    }

    public function lsp_register_widgets() {
        require_once LSP_PLUGIN_DIR . 'includes/widget/class-lsp-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new \LanguageSwitcherPolylangElementorWidget\LSP\LSP_Widget());
    }

    public function lsp_register_styles() {
        
    }
}

new LSP_Register_Widget();