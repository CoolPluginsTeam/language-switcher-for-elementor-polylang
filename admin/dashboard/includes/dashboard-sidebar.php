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

 $polylang_support = esc_url("https://my.coolplugins.net/account/support-tickets/");
?>

<div class="cool-body-right">
<ul>
      <li>Add the Language Switcher for Elementor or Divi Sites.</li>
      <li>Automatic Translations of Site Content in Multiple Languages.</li>
      <li>Duplicate Content Addon for Polylang.</li>
      </ul>    
      <br/>
      <a href="<?php echo esc_url($polylang_support); ?>" target="_blank" class="button button-primary">👉 Plugin Support</a>
      <br/><br/>
</div>

</div><!-- End of main container-->