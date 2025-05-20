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
	public static function get_flag_code( $flag_url ) {
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
	public static function get_country_flag( $flag_url, $lang ) {
		$country_code = self::get_flag_code( $flag_url );
		$flag         = array();

		if ( $country_code && class_exists( 'PLL_Language' ) && method_exists( 'PLL_Language', 'get_flag_html' ) ) {
			$flag['path'] = LSEP_PLUGIN_DIR . 'assets/flags/' . esc_html( $country_code ) . '.svg';
			$flag['url']  = esc_url( LSEP_PLUGIN_URL . 'assets/flags/' . esc_html( $country_code ) . '.svg' );
			$flag['src'] = $flag['url'];
			$flag_html = \PLL_Language::get_flag_html( $flag, '', $lang );
			return $flag_html;
		}

		$flag['src'] = $flag_url;
		$flag_html   = \PLL_Language::get_flag_html( $flag, '', $lang );
		return $flag_html;
	}
}
