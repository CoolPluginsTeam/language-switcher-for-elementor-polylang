<?php
if (!defined('ABSPATH')) {
    exit;
} 
/**
 * 
 * This is the main class for creating dashbord addon page and all submenu items
 * 
 * Do not call or initialize this class directly, instead use the function mentioned at the bottom of this file
 */

    class cool_plugins_lsep_polylang_addons
    {

        /**
         * None of these variables should be accessable from the outside of the class
         */
            private static $instance;
            private $pro_plugins = array();
            private $main_menu_slug = null;// 'cool-plugins-polylang-addon';
            private $plugin_tag = null;
            private $disable_plugins = array();
            private $addon_dir = __DIR__;    // point to the main addon-page directory
            private $plugin_api = 'https://plugins.coolplugins.net/plugins-list/';

            /**
             * initialize the class and create dashboard page only one time
             */
            public static function init( ){

                if( empty(self::$instance) ){
                    return self::$instance = new self;
                }
                return self::$instance;

            }


            /**
             * Initialize the dashboard with specific plugins as per plugin tag
             * 
             */
            public function show_plugins( $plugin_tag , $menu_slug , $dashboard_heading ){
                if( !empty($plugin_tag) && !empty($menu_slug) && !empty($dashboard_heading) ){
                    $this->plugin_tag = $plugin_tag;
                    $this->main_menu_slug = $menu_slug;
                }else{
                    return false;
                }
                add_action('admin_menu', array($this, 'init_plugins_dasboard_page'), 10);
                add_action('wp_ajax_cool_plugins_install_'. $this->plugin_tag, array($this, 'cool_plugins_install'));
                add_action('wp_ajax_cool_plugins_activate_'. $this->plugin_tag, array($this, 'cool_plugins_activate'));
                add_action('admin_enqueue_scripts', array($this,'enqueue_required_scripts') );
                add_action( 'in_admin_header', array( $this, 'suppress_foreign_admin_notices' ), 1000 );
            }

            /**
             * Whether the current request is rendering this plugin dashboard.
             *
             * @return bool
             */
            private function is_dashboard_page() {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for page detection.
                $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

                return 'lsep-get-started' === $page;
            }

            /**
             * Remove third-party plugin and theme admin notices on this dashboard.
             */
            public function suppress_foreign_admin_notices() {
                if ( ! $this->is_dashboard_page() ) {
                    return;
                }

                remove_all_actions( 'admin_notices' );
                remove_all_actions( 'all_admin_notices' );
                remove_all_actions( 'user_admin_notices' );
            }

            /**
             * handle ajax request for activating plugin from dashboard
             */
            function cool_plugins_activate(){
                if(current_user_can('upload_plugins')){
                   
                $plugin_slug= isset($_POST["polylang_activate_slug"])?sanitize_text_field(wp_unslash($_POST["polylang_activate_slug"])):'';
                
                $wp_nonce = 'polylang-plugins-activate-' . $plugin_slug ;
                if(!empty( $plugin_slug)){
                    if ( ! check_ajax_referer($wp_nonce,'wp_nonce', false ) ) {
                        wp_send_json_error( 'Invalid security token sent.' );
                        wp_die();
                    }
                $pluginBase = ( isset( $_POST['polylang_activate_pluginbase'] ) && !empty( $_POST['polylang_activate_pluginbase'] ) )? sanitize_text_field(wp_unslash($_POST['polylang_activate_pluginbase'])) : null;
                
                $plugin_base_arr=explode("/",$pluginBase);
                if( isset($plugin_base_arr[0]) && $plugin_base_arr[0]==$plugin_slug ){
                    activate_plugin( $pluginBase );
                  
                }else{
                    wp_send_json_error( 'Something wrong with plugin path.' );
                    wp_die();
                }
                }else{
                    wp_send_json_error( 'Plugin slug is missing.' );
                    wp_die();  
                }
                }else{
                    wp_send_json_error( 'You have no permission to do this action.' );
                    wp_die();  
                }
            }

            /**
             * handle ajax for installing plugin from the dashboard.
             * This function use the core wordpress functionality of installing a plugin through URL
             */
            function cool_plugins_install(){
            if(current_user_can('upload_plugins')){
                $plugin_slug= isset($_POST['polylang_slug'])?sanitize_text_field(wp_unslash($_POST['polylang_slug'])):'';
                $wp_nonce = wp_create_nonce('polylang-plugins-download-' . $plugin_slug );
                if(!empty( $plugin_slug)){
                    if ( ! check_ajax_referer( 'polylang-plugins-download-' . $plugin_slug,'wp_nonce', false ) ) {
                  
                        wp_send_json_error( 'Invalid security token sent.' );
                        wp_die();
                    }
                  
                    require_once 'includes/cool_plugins_downloader.php';
                        $downloader = new cool_plugins_downloader();
                      
                        $plugins = $this->request_wp_plugins_data($this->plugin_tag);
                       
                        if(isset($plugins[$plugin_slug])){
                            $url=$plugins[$plugin_slug]['download_link'];
                            return  $downloader->install( filter_var($url, FILTER_SANITIZE_URL), 'install' );
                        
                        }
                else{
                    wp_send_json_error( 'Sorry, You are installing a wrong plugin.' );
                    wp_die();
                }
            }else{
                wp_send_json_error( 'Plugin slug is missing.' );
                wp_die();  
            }
            }else{
                wp_send_json_error( 'You have no permission to do this action.' );
                wp_die();  
            }
            }


            /**
             * This function will initialize the main dashboard page for all plugins
             */
            function init_plugins_dasboard_page(){
                add_submenu_page(
                    'mlang',
                    __('Get Started', 'language-switcher-for-elementor-polylang'),
                    __('Get Started', 'language-switcher-for-elementor-polylang'),
                    'manage_options',
                    'lsep-get-started',
                    array($this, 'displayPluginAdminDashboard')
                );
            }

            /**
             * This function will render and create the HTML display of dashboard page.
             * All the HTML can be located in other template files.
             * Avoid using any HTML here or use nominal HTML tags inside this function.
             */
            function displayPluginAdminDashboard(){
                if ( ! current_user_can( 'manage_options' ) ) {
                    return;
                }

                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for tab display.
                $current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'getting-started';
                $page_url    = admin_url( 'admin.php?page=lsep-get-started' );
                $logo_url    = plugin_dir_url( __FILE__ ) . 'assets/images/language-switcher-for-elementor-polylang.png';

                echo '<div class="wrap lsep-dashboard-wrap">';

                echo '<div class="lsep-dashboard-header">';
                echo '<div class="lsep-header-content">';
                echo '<div class="lsep-header-logo">';
                echo '<img src="' . esc_url( $logo_url ) . '" alt="" />';
                echo '<h1 class="lsep-header-title">' . esc_html__( 'Language Switcher for Elementor & Polylang', 'language-switcher-for-elementor-polylang' ) . '</h1>';
                echo '</div>';
                echo '<div class="lsep-header-actions">';
                echo '<a href="' . esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/#new-topic-0' ) . '" class="button button-secondary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get Support', 'language-switcher-for-elementor-polylang' ) . '</a>';
                echo '<a href="' . esc_url( 'https://docs.coolplugins.net/doc/language-switcher-for-elementor-polylang/?utm_source=lsep_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_header' ) . '" class="button button-secondary" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'language-switcher-for-elementor-polylang' ) . '</a>';
                echo '<a href="' . esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/reviews/#new-post' ) . '" class="button button-primary" target="_blank" rel="noopener noreferrer">' . esc_html__( '★★★★★ Rate Now', 'language-switcher-for-elementor-polylang' ) . '</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';

                echo '<h2 class="nav-tab-wrapper">';
                echo '<a href="' . esc_url( add_query_arg( 'tab', 'getting-started', $page_url ) ) . '" class="nav-tab' . ( 'getting-started' === $current_tab ? ' nav-tab-active' : '' ) . '">' . esc_html__( 'Get Started', 'language-switcher-for-elementor-polylang' ) . '</a>';
                echo '<a href="' . esc_url( add_query_arg( 'tab', 'floating-switcher', $page_url ) ) . '" class="nav-tab' . ( 'floating-switcher' === $current_tab ? ' nav-tab-active' : '' ) . '">' . esc_html__( 'Floating Language Switcher', 'language-switcher-for-elementor-polylang' ) . '</a>';
                echo '<a href="' . esc_url( add_query_arg( 'tab', 'more-addons', $page_url ) ) . '" class="nav-tab' . ( 'more-addons' === $current_tab ? ' nav-tab-active' : '' ) . '">' . esc_html__( 'More Addons', 'language-switcher-for-elementor-polylang' ) . '</a>';
                echo '</h2>';

                echo '<div class="lsep-tab-content-wrapper">';
                if ( 'floating-switcher' === $current_tab ) {
                    $this->floating_switcher_content();
                } elseif ( 'more-addons' === $current_tab ) {
                    $this->moreaddons_plugins_data();
                } else {
                    $this->get_started_content();
                }
                echo '</div>';

                echo '</div>';
            }

            function floating_switcher_content() {
                $lsep_languages = function_exists( 'pll_languages_list' ) ? pll_languages_list() : array();

                if ( empty( $lsep_languages ) ) {
                    echo '<div class="notice notice-warning"><p>';
                    echo '<strong>' . esc_html__( 'No languages configured!', 'language-switcher-for-elementor-polylang' ) . '</strong><br>';
                    echo esc_html__( 'Please configure at least two languages in Polylang settings.', 'language-switcher-for-elementor-polylang' );
                    echo '</p></div>';
                }

                echo '<div id="lsep-floater-app-root"></div>';
            }

            function get_started_content(){
                require $this->addon_dir . '/includes/get-started-content.php';
            }

            function moreaddons_plugins_data(){
                $tag = $this->plugin_tag;
                $plugins = $this->request_wp_plugins_data( $tag );
                $this->request_pro_plugins_data( $tag );
                $this->polylang_disable_free_plugins();
                if( !empty( $plugins ) && count( $plugins ) > 0 ){

                    // merge free & pro plugins into one array
                    if( count($this->pro_plugins) > 0 ){
                        $plugins = array_merge($plugins, $this->pro_plugins);
                    }

                    echo '<div id="cool-plugins-container" class="' . esc_attr( $this->main_menu_slug ) . '">';
                    echo '<div class="cool-body-left">';
                    echo '<div class="plugins-list installed-addons" data-empty-message="' . esc_attr__( 'You have not installed any addon at the moment', 'language-switcher-for-elementor-polylang' ) . '"><h3>' . esc_html__( 'Currently Installed Addons', 'language-switcher-for-elementor-polylang' ) . '</h3>';

                    foreach($plugins as $plugin ){

                        $plugin_name = $plugin['name'];
                        $plugin_desc = $plugin['desc'];
                        $plugin_logo =$this->polylang_addon_plugins_logo($plugin['slug']);
                        $plugin_url = $plugin['download_link'];
                        $plugin_slug = $plugin['slug'];
                        $plugin_version = $plugin['version'];
 
                        if( file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ){
                            require $this->addon_dir . '/includes/dashboard-page.php';
                        }

                    }
                    echo "</div>";

                    echo '<div class="plugins-list more-addons" data-empty-message="' . esc_attr__( 'No more free addons available at the moment', 'language-switcher-for-elementor-polylang' ) . '"><h3>' . esc_html__( 'More Addons', 'language-switcher-for-elementor-polylang' ) . '</h3>';
                    foreach($plugins as $plugin ){

                        if( $plugin['download_link'] == null ){
                            continue;
                        }
                        $plugin_name = $plugin['name'];
                        $plugin_desc = $plugin['desc'];
                        $plugin_logo =$this->polylang_addon_plugins_logo($plugin['slug']);
                        $plugin_url = $plugin['download_link'];
                        $plugin_slug = $plugin['slug'];
                        $plugin_version = $plugin['version'];
                        
                        if( !file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ){
                            require $this->addon_dir . '/includes/dashboard-page.php';
                        }

                    }
                    echo '</div>';
                    if( !empty($this->pro_plugins) && count($this->pro_plugins) >0 ):
                        /**
                         * Load this Pro Plugin container only if there are any pro plugins available
                         */
                    echo '<div class="plugins-list pro-addons" data-empty-message="' . esc_attr__( 'No more Pro plugins available at the moment', 'language-switcher-for-elementor-polylang' ) . '"><h3>' . esc_html__( 'Pro Addons', 'language-switcher-for-elementor-polylang' ) . '</h3>';
                        foreach($this->pro_plugins as $plugin ){
                             $plugin_name = $plugin['name'];
                            $plugin_desc = $plugin['desc'];
                            $plugin_logo =$this->polylang_addon_plugins_logo($plugin['slug']);
                            $plugin_pro_url = $plugin['buyLink'];
                            $plugin_url = null;
                            $plugin_version = null;
                            $plugin_slug = $plugin['slug'];
                            
                            if( !file_exists( WP_PLUGIN_DIR . '/' . $plugin_slug ) ){
                                require $this->addon_dir . '/includes/dashboard-page.php';
                            }

                        }
                        echo '</div>';
                    endif;
                    echo '</div>'; // .cool-body-left
                    require $this->addon_dir . '/includes/dashboard-sidebar.php';

                }else{
                    // plugins are not available under this tag.
                }
            }

            /**
             * Lets enqueue all the required CSS & JS
             *
             * @param string $hook Current admin page hook suffix.
             */
            function enqueue_required_scripts( $hook ) {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET used only for conditional asset loading.
                $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

                if ( 'lsep-get-started' !== $page && false === strpos( $hook, 'lsep-get-started' ) ) {
                    return;
                }

                wp_enqueue_style( 'cool-lsep-plugins-polylang-addon', plugin_dir_url( __FILE__ ) . 'assets/css/styles.css', null, LSEP_VERSION, 'all' );
                wp_enqueue_script( 'cool-lsep-plugins-polylang-addon', plugin_dir_url( __FILE__ ) . 'assets/js/script.js', array( 'jquery' ), LSEP_VERSION, true );
                wp_localize_script( 'cool-lsep-plugins-polylang-addon', 'lsep_polylang', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
            }


    /**
         * This function will gather all information regarding pro plugins.
         */
        public function request_pro_plugins_data($tag = null)
        {
            $trans_name = $this->main_menu_slug . '_pro_api_cache' . $this->plugin_tag;
            $option_name = $this->main_menu_slug . '-' . $this->plugin_tag . '-pro';
            if (get_transient($trans_name) != false) {

                return $this->pro_plugins = get_option($option_name, false);
            }
            $url = $this->plugin_api . 'pro/' . $this->plugin_tag;

            $pro_api = esc_url($url);
            $response = wp_remote_get($pro_api, array('timeout' => 300));
            if (is_wp_error($response)) {
                return;
            }
            $plugin_info = (array) json_decode($response['body']);

            foreach ($plugin_info as $plugin) {
              if ($plugin->name) {

                    $this->pro_plugins[$plugin->slug] = array(
                        'name' => $plugin->name,
                        'logo' => $plugin->image_url,
                        'desc' => $plugin->info,
                        'slug' => $plugin->slug,
                        'buyLink' => $plugin->buy_url,
                        'version' => $plugin->version,
                        'download_link' => null,
                        'incompatible' => $plugin->free_version,
                        'buyLink' => $plugin->buy_url,
                    );
                    if (property_exists($plugin, 'free_version') && $plugin->free_version != null) {
                        $this->disable_plugins[$plugin->free_version] = array('pro' => $plugin->slug);
                    }
               }

            }


            if (!empty($this->pro_plugins) && is_array($this->pro_plugins) && count($this->pro_plugins)) {
                set_transient($trans_name, $this->pro_plugins, DAY_IN_SECONDS);
                update_option($option_name, $this->pro_plugins);
                return $this->pro_plugins;
            } else if (get_option($option_name, false) != false) {
                return get_option($option_name);
            }

        }


           
        /**
         * Gather all the free plugin information from wordpress.org API
         */
        public function request_wp_plugins_data($tag = null)
        {

            if (get_transient($this->main_menu_slug . '_api_cache' . $this->plugin_tag) != false) {
                return get_option($this->main_menu_slug . '-' . $this->plugin_tag, false);
            }
             $url = $this->plugin_api . 'free/' . $this->plugin_tag;


            $response = wp_remote_get($url, array('timeout' => 300));
            if (is_wp_error($response)) {
                return;
            }
            $plugin_info = json_decode($response['body'],true);
            $all_plugins = array();
            foreach ($plugin_info as $plugin) {
                $plugins_data['name'] = $plugin['name'];
                $plugins_data['logo'] = $plugin['image_url'];
                $plugins_data['slug'] = $plugin['slug'];
                $plugins_data['desc'] = $plugin['info'];
                $plugins_data['version'] = $plugin['version'];
                $plugins_data['tags'] = $plugin['tag'];
                $plugins_data['download_link'] = $plugin['download_url'];
                $all_plugins[$plugin['slug']] = $plugins_data;
            }
           

            if (!empty($all_plugins) && is_array($all_plugins) && count($all_plugins)) {
                set_transient($this->main_menu_slug . '_api_cache' . $this->plugin_tag, $all_plugins, DAY_IN_SECONDS);
                update_option($this->main_menu_slug . '-' . $this->plugin_tag, $all_plugins);
                return $all_plugins;
            } elseif (get_option($this->main_menu_slug . '-' . $this->plugin_tag, false) != false) {
                return get_option($this->main_menu_slug . '-' . $this->plugin_tag);
            }
        }
   
    function polylang_addon_plugins_logo($slug){
        $logos_arr=[
            'language-switcher-for-elementor-polylang' => 'language-switcher-for-elementor-polylang.png',
            'language-switcher-for-divi-polylang' => 'language-switcher-for-divi-polylang.png',
            'automatic-translations-for-polylang' => 'automatic-translations-for-polylang.png',
            'duplicate-content-addon-for-polylang' => 'duplicate-content-addon-for-polylang.png',
            'autopoly-ai-translation-for-polylang-pro' => 'autopoly-ai-translation-for-polylang-pro.png',
        ];
        if(isset($logos_arr[$slug])){
            return $logo_url= plugin_dir_url( __FILE__ ).'assets/images/'.$logos_arr[$slug];
        }
        
    }
    function polylang_disable_free_plugins() {
        if ( isset( $this->pro_plugins ) ) {
            foreach ( $this->pro_plugins as  $plugin ) {
                if ( isset( $plugin['incompatible'] ) && $plugin['incompatible'] != null ) {
                    $this->disable_plugins[ $plugin['incompatible'] ] = array( 'pro' => $plugin['slug'] );
                }
            }
        }
    }   
}

    /**
     * 
     * initialize the main dashboard class with all required parameters
     */

    function cool_plugins_lsep_polylang_addon_settings_page($tag ,$settings_page_slug, $dashboard_heading ){
        $polylang_page = cool_plugins_lsep_polylang_addons::init();
        $polylang_page->show_plugins( $tag, $settings_page_slug, $dashboard_heading );

    }



