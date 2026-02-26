<?php
if (!defined('ABSPATH')) {
  exit;
} 
/**
 *
 * This page serve as dashboard template
 *
 */
// do not render this page if its found outside of main class
if( !isset($this->main_menu_slug) ){
  return false;
}
$lsep_is_active = false;
$lsep_classes = 'plugin-block';
$lsep_is_installed = false;
$lsep_button = null;
$lsep_available_version = null;
$lsep_update_available = false;
$lsep_update_stats = '';
$lsep_pro_already_installed = false;

// Let's see if a pro version is already installed
if( isset( $this->disable_plugins[ $plugin_slug ] ) ){
    $lsep_pro_version = $this->disable_plugins[ $plugin_slug ];
    if( file_exists(WP_PLUGIN_DIR .'/' . $lsep_pro_version['pro'] ) ){
        $lsep_pro_already_installed = true;
        $lsep_classes .= ' plugin-not-required';
    }
}

if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {

    $lsep_is_installed = true;
    $lsep_plguin_file = null;
    $lsep_installed_plugins = get_plugins();//get_option('active_plugins', false);
    $lsep_is_active = false;
    $lsep_classes .= ' installed-plugin';
    $lsep_plugin_file = null;
    foreach ($lsep_installed_plugins as $plugin=>$lsep_data) {
      $lsep_thisPlugin = substr($plugin,0,strpos($plugin,'/'));
      if ( strcasecmp($lsep_thisPlugin, $plugin_slug) == 0 ) {

          if( isset($lsep_plugin_version) && version_compare( $lsep_plugin_version, $lsep_data['Version'] ) >0 ){
            $lsep_available_version = $lsep_plugin_version ;
            $lsep_plugin_version =  $lsep_data['Version'];
            $lsep_update_stats = '<span class="plugin-update-available">Update Available: v '.wp_kses_post($lsep_available_version).'</span>';
          }

          if( is_plugin_active($plugin) ){
            $lsep_is_active = true;
            $lsep_classes .= ' active-plugin';
            break;
          }else{
            $lsep_plugin_file = $plugin;
            $lsep_classes .= ' inactive-plugin';
          }

        }
    }
    if( $lsep_is_active ){
        $lsep_button = '<button class="button button-disabled">Active</button>';
    }else{
        $lsep_wp_nonce = wp_create_nonce( 'polylang-plugins-activate-' . $plugin_slug );
        $lsep_button .= '<button class="button activate-now cool-plugins-addon plugin-activator" data-plugin-tag="'.esc_attr($tag).'" data-plugin-id="'.esc_attr($lsep_plugin_file).'" 
        data-action-nonce="'.esc_attr($lsep_wp_nonce).'" data-plugin-slug="'.esc_attr($plugin_slug).'">Activate</button>';
    }
} else {
    $lsep_wp_nonce = wp_create_nonce('polylang-plugins-download-' . $plugin_slug );
    $lsep_classes .= ' available-plugin';
    if( $plugin_url !=null ){
      $lsep_button = '<button class="button install-now cool-plugins-addon plugin-downloader" data-plugin-tag="'.esc_attr($tag).'"  data-action-nonce="' .esc_attr($lsep_wp_nonce) . '" data-plugin-slug="'.esc_attr($plugin_slug).'">Install</button>';
    
    }elseif( isset($plugin_pro_url) ){
      $lsep_button = '<a class="button install-now cool-plugins-addon pro-plugin-downloader" href="'.esc_url($plugin_pro_url).'" target="_new">Buy Pro</a>';
    }
}

// Remove install / activate button if pro version is already installed
if( $lsep_pro_already_installed === true ){
  $lsep_pro_ver = $this->disable_plugins[ $plugin_slug ] ;
  $lsep_button = '<button class="button button-disabled" title="This plugin is no more required as you already have '.esc_attr($lsep_pro_ver['pro']).'">Pro Installed</button>';
}

    // All php condition formation is over here
?>



<div class="<?php echo esc_attr($lsep_classes); ?>">
  <div class="plugin-block-inner">

    <div class="plugin-logo">
    <img src="<?php echo esc_url($plugin_logo); ?>" width="250px" />
    </div>

    <div class="plugin-info">
      <h4 class="plugin-title"> <?php echo esc_html($plugin_name); ?></h4>
      <div class="plugin-desc"><?php echo esc_html($plugin_desc); ?></div>
      <div class="plugin-stats">
      <?php echo wp_kses_post($lsep_button) ; ?> 
      <?php if( isset($lsep_plugin_version) && !empty($lsep_plugin_version)) : ?>
        <div class="plugin-version">v <?php echo esc_html($lsep_plugin_version); ?></div>
        <?php echo wp_kses_post($lsep_update_stats); ?>
      <?php endif; ?>
      </div>
    </div>

  </div>
</div>
