<?php
/**
 * Language Switcher Polylang Elementor Helpers Class
 *
 * @package Language_Switcher_Polylang_Elementor
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class LSEP_HELPERS
 *
 * Helper functions for Language Switcher Polylang Elementor plugin.
 *
 * @since 1.0.0
 */
class LSEP_HELPERS {

	/**
	 * Extract flag code from flag URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $flag_url The URL of the flag image.
	 * @return string|false The flag code if found, false otherwise.
	 */
	public static function lsep_get_flag_code( $flag_url ) {
		$flag_code = preg_match( '/polylang\/flags\/([a-z]+)\.(png|svg|jpg|jpeg)$/i', $flag_url, $matches ) ? $matches[1] : false;
		return $flag_code;
	}

	/**
	 * Get country flag HTML for a specific language.
	 *
	 * @since 1.0.0
	 *
	 * @param string $flag_url The URL of the flag image.
	 * @param string $lang     The language code.
	 * @return string The HTML markup for the flag.
	 */
	public static function lsep_get_country_flag( $flag_url, $lang ) {
		$country_code = self::lsep_get_flag_code( $flag_url );
		$flag         = [];

		if ( $country_code && class_exists( 'PLL_Language' ) && method_exists( 'PLL_Language', 'get_flag_html' ) ) {
			$flag['path'] = LSEP_PLUGIN_DIR . 'assets/flags/' . esc_html( $country_code ) . '.svg';
			$flag['url']  = esc_url( LSEP_PLUGIN_URL . 'assets/flags/' . esc_html( $country_code ) . '.svg' );
			$flag['src']  = $flag['url'];
			$flag_html    = \PLL_Language::get_flag_html( $flag, '', $lang );
			return $flag_html;
		}

		$flag['src'] = $flag_url;
		$flag_html   = \PLL_Language::get_flag_html( $flag, '', $lang );
		return $flag_html;
	}

	/**
	 * Convert Polylang PNG flag URL to plugin's SVG flag URL.
	 * Consolidated method to avoid duplication across frontend and admin.
	 *
	 * @since 1.2.4
	 *
	 * @param string $polylang_flag_url Polylang flag URL.
	 * @return string Plugin's SVG flag URL or original if not found.
	 */
	public static function get_plugin_flag_url( $polylang_flag_url ) {
		if ( empty( $polylang_flag_url ) ) {
			return '';
		}
		
		// Get flag code from Polylang URL
		$flag_code = self::lsep_get_flag_code( $polylang_flag_url );
		
		if ( empty( $flag_code ) ) {
			return $polylang_flag_url; // Fallback to original
		}
		
		// Build path to plugin's SVG flag
		return LSEP_PLUGIN_URL . 'assets/flags/' . $flag_code . '.svg';
	}

	/**
	 * Check if required plugin dependencies are active.
	 * Consolidated check to avoid repetition.
	 *
	 * @since 1.2.4
	 *
	 * @return bool True if Polylang and Elementor are active.
	 */
	public static function is_dependencies_active() {
		global $polylang;
		
		// Check if Polylang is loaded
		if ( ! isset( $polylang ) ) {
			return false;
		}
		
		// Check if Elementor is active
		if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
			return false;
		}
		
		return true;
	}

	/**
	 * Get language name based on display mode.
	 * Consolidated method to format language names consistently.
	 *
	 * @since 1.2.4
	 *
	 * @param array  $lang Language data from Polylang.
	 * @param string $mode Display mode: 'full', 'short', or 'none'.
	 * @return string Formatted language name.
	 */
	public static function get_language_name( $lang, $mode ) {
		switch ( $mode ) {
			case 'full':
				return $lang['name'];
			case 'short':
				return strtoupper( $lang['slug'] );
			case 'none':
			default:
				return '';
		}
	}
}