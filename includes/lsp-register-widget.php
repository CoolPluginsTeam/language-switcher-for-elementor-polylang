<?php
if(!defined('ABSPATH')){
	exit;
}


class LSP_Register_Widget {
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'lsp_register_widgets']);
    }

    public function lsp_register_widgets() {
        require_once LSP_PLUGIN_DIR . 'includes/widget/class-lsp-widget.php';
    }
}

new LSP_Register_Widget();