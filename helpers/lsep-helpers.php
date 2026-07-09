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
	 * Accepts a Polylang flag URL or a plain country-code string (e.g. "us").
	 *
	 * @since 1.0.0
	 *
	 * @param string $flag_value Flag URL or country code.
	 * @param string $lang_name  Language name for alt text.
	 * @return string The HTML markup for the flag.
	 */
	public static function lsep_get_country_flag( $flag_value, $lang_name ) {
		if ( empty( $flag_value ) || empty( $lang_name ) ) {
			return '';
		}

		if ( false === strpos( $flag_value, '/' ) ) {
			$country_code = sanitize_file_name( $flag_value );
		} else {
			$country_code = self::lsep_get_flag_code( $flag_value );
		}

		if ( empty( $country_code ) ) {
			if ( class_exists( 'PLL_Language' ) && method_exists( 'PLL_Language', 'get_flag_html' ) ) {
				$flag = array( 'src' => $flag_value );
				return \PLL_Language::get_flag_html( $flag, '', $lang_name );
			}
			return '';
		}

		$flag_url  = LSEP_PLUGIN_URL . 'assets/flags/' . $country_code . '.svg';
		$flag_path = LSEP_PLUGIN_DIR . 'assets/flags/' . $country_code . '.svg';

		if ( class_exists( 'PLL_Language' ) && method_exists( 'PLL_Language', 'get_flag_html' ) ) {
			$flag = array(
				'path' => $flag_path,
				'url'  => esc_url( $flag_url ),
				'src'  => esc_url( $flag_url ),
			);
			return \PLL_Language::get_flag_html( $flag, '', $lang_name );
		}

		return sprintf(
			'<img src="%s" alt="%s" loading="lazy" />',
			esc_url( $flag_url ),
			esc_attr( $lang_name )
		);
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

		if ( false === strpos( $polylang_flag_url, '/' ) ) {
			return LSEP_PLUGIN_URL . 'assets/flags/' . sanitize_file_name( $polylang_flag_url ) . '.svg';
		}

		$flag_code = self::lsep_get_flag_code( $polylang_flag_url );

		if ( empty( $flag_code ) ) {
			return $polylang_flag_url;
		}

		return LSEP_PLUGIN_URL . 'assets/flags/' . $flag_code . '.svg';
	}

	/**
	 * Get Polylang languages as a normalized array.
	 *
	 * @since 1.2.5
	 * @return array[] Each item contains slug, name, flag_url, and locale.
	 */
	public static function get_polylang_languages() {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return array();
		}

		$pll_languages = pll_languages_list( array( 'fields' => false ) );
		if ( empty( $pll_languages ) ) {
			return array();
		}

		$languages = array();
		foreach ( $pll_languages as $lang ) {
			$languages[] = array(
				'slug'     => $lang->slug,
				'name'     => $lang->name,
				'flag_url' => $lang->flag_url ?? '',
				'locale'   => $lang->locale,
			);
		}

		return $languages;
	}

	/**
	 * Get default languages when Polylang is unavailable.
	 *
	 * @since 1.2.5
	 * @return array[] Normalized language items.
	 */
	public static function get_default_languages() {
		return array(
			array(
				'slug'     => 'en',
				'name'     => __( 'English', 'language-switcher-for-elementor-polylang' ),
				'flag_url' => 'us',
				'locale'   => 'en_US',
			),
			array(
				'slug'     => 'fr',
				'name'     => __( 'Français', 'language-switcher-for-elementor-polylang' ),
				'flag_url' => 'fr',
				'locale'   => 'fr_FR',
			),
		);
	}

	/**
	 * Get Polylang languages, falling back to defaults when needed.
	 *
	 * @since 1.2.5
	 * @return array[] Normalized language items.
	 */
	public static function get_polylang_languages_with_fallback() {
		$languages = self::get_polylang_languages();

		return ! empty( $languages ) ? $languages : self::get_default_languages();
	}

	/**
	 * Get languages for admin UIs (floating switcher app).
	 *
	 * @since 1.2.5
	 * @return array[] Each item contains code, name, flag, and locale.
	 */
	public static function get_polylang_languages_for_admin() {
		$languages = array();

		foreach ( self::get_polylang_languages_with_fallback() as $language ) {
			$languages[] = array(
				'code'   => $language['slug'],
				'name'   => $language['name'],
				'flag'   => self::get_plugin_flag_url( $language['flag_url'] ),
				'locale' => $language['locale'],
			);
		}

		return $languages;
	}

	/**
	 * Get languages for the floating switcher frontend.
	 *
	 * @since 1.2.5
	 * @param string $name_mode Display mode: full, short, or none.
	 * @return array[] Each item contains code, name, url, flag, and is_current.
	 */
	public static function get_floater_languages( $name_mode = 'full' ) {
		if ( ! function_exists( 'pll_the_languages' ) || ! function_exists( 'pll_current_language' ) ) {
			return array();
		}

		$current_lang  = pll_current_language();
		$raw_languages = pll_the_languages(
			array(
				'raw'           => 1,
				'hide_if_empty' => 0,
			)
		);

		if ( empty( $raw_languages ) ) {
			return array();
		}

		$languages = array();

		foreach ( $raw_languages as $lang ) {
			$lang_data = array(
				'code'       => $lang['slug'],
				'name'       => self::get_language_name( $lang, $name_mode ),
				'url'        => $lang['url'],
				'flag'       => self::get_plugin_flag_url( $lang['flag'] ?? '' ),
				'is_current' => $lang['slug'] === $current_lang,
			);

			if ( $lang_data['is_current'] ) {
				array_unshift( $languages, $lang_data );
			} else {
				$languages[] = $lang_data;
			}
		}

		return $languages;
	}

	/**
	 * Get a Polylang language by slug, with fallback defaults.
	 *
	 * @since 1.2.5
	 * @param string $slug Language slug.
	 * @return array|null Normalized language data or null when not found.
	 */
	public static function get_polylang_language_by_slug( $slug ) {
		foreach ( self::get_polylang_languages_with_fallback() as $language ) {
			if ( $language['slug'] === $slug ) {
				return $language;
			}
		}

		return null;
	}

	/**
	 * Get Polylang languages formatted for block editor select controls.
	 *
	 * @since 1.2.5
	 * @return array[] Select options with value and label keys.
	 */
	public static function get_polylang_language_select_options() {
		$options = array();

		foreach ( self::get_polylang_languages_with_fallback() as $language ) {
			$options[] = array(
				'value' => $language['slug'],
				'label' => $language['name'] . ' (' . $language['slug'] . ')',
			);
		}

		return $options;
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

	/**
	 * Get AutoPoly plugin file path.
	 *
	 * @since 1.2.4
	 * @return string Plugin file path.
	 */
	public static function get_autopoly_plugin_file() {
		return 'automatic-translations-for-polylang/automatic-translation-for-polylang.php';
	}

	/**
	 * Check AutoPoly plugin status.
	 *
	 * @since 1.2.4
	 * @return array Status with 'installed' and 'active' booleans.
	 */
	public static function get_autopoly_status() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_file = self::get_autopoly_plugin_file();
		$all_plugins = get_plugins();

		return array(
			'installed' => isset( $all_plugins[ $plugin_file ] ),
			'active'    => is_plugin_active( $plugin_file ),
		);
	}
}