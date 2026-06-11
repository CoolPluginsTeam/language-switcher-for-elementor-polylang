<?php
if (!defined('ABSPATH')) {
   exit;
} 
/**
 * 
 * Addon dashboard sidebar.
 */

 if( !isset($this->main_menu_slug) ):
    return false;
 endif;

 $lsep_polylang_support = esc_url("https://my.coolplugins.net/account/support-tickets/");
?>

<div class="cool-body-right">
<ul>
      <li><?php echo esc_html__( 'Add the Language Switcher for Elementor or Divi Sites.', 'language-switcher-for-elementor-polylang' ); ?></li>
      <li><?php echo esc_html__( 'Automatic Translations of Site Content in Multiple Languages.', 'language-switcher-for-elementor-polylang' ); ?></li>
      <li><?php echo esc_html__( 'Duplicate Content Addon for Polylang.', 'language-switcher-for-elementor-polylang' ); ?></li>
      </ul>    
      <br/>
      <a href="<?php echo esc_url($lsep_polylang_support); ?>" target="_blank" class="button button-primary"><?php echo esc_html__( '👉 Plugin Support', 'language-switcher-for-elementor-polylang' ); ?></a>
      <br/><br/>
</div>

</div><!-- End of main container-->