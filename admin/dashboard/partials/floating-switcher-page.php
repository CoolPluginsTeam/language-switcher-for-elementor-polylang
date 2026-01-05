<?php
/**
 * Floating Switcher Settings Page
 * Standalone page - scripts load properly here
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html__( 'Floating Language Switcher', 'language-switcher-for-elementor-polylang' ); ?></h1>
    
    <?php
    // Check Polylang
    $lsep_languages = function_exists( 'pll_languages_list' ) ? pll_languages_list() : [];
    
    if ( empty( $lsep_languages ) ) :
    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php echo esc_html__( 'No languages configured!', 'language-switcher-for-elementor-polylang' ); ?></strong><br>
            <?php echo esc_html__( 'Please configure at least two languages in Polylang settings.', 'language-switcher-for-elementor-polylang' ); ?>
        </p>
    </div>
    <?php endif; ?>
    
    <div id="lsep-floater-app-root"></div>
</div>