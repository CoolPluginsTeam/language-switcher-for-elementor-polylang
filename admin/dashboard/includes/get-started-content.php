<?php
/**
 * Get Started tab content.
 *
 * @package Language_Switcher_For_Elementor_Polylang
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$assets_url = LSEP_PLUGIN_URL . 'assets/images/';
$video_id   = 'HyM0woo9Cg0';

$dashboard = class_exists( 'cool_plugins_lsep_polylang_addons' ) ? cool_plugins_lsep_polylang_addons::init() : null;

$has_elementor = class_exists( 'LSEP_HELPERS' ) && LSEP_HELPERS::lsep_is_plugin_active( 'elementor/elementor.php' );
$has_divi      = $dashboard ? $dashboard->is_divi_available() : (
	( class_exists( 'LSEP_HELPERS' ) && LSEP_HELPERS::lsep_is_plugin_active( 'divi-builder/divi-builder.php' ) )
	|| ( function_exists( 'wp_get_theme' ) && 'Divi' === wp_get_theme()->get_template() )
	|| defined( 'ET_BUILDER_THEME' )
	|| defined( 'ET_BUILDER_PLUGIN_ACTIVE' )
);

$show_builder_picker = $has_elementor || $has_divi;
$saved_builder       = $dashboard ? $dashboard->get_preferred_builder() : '';

if ( $saved_builder ) {
	$default_builder = $saved_builder;
} elseif ( $has_elementor ) {
	$default_builder = 'elementor';
} elseif ( $has_divi ) {
	$default_builder = 'divi';
} else {
	$default_builder = 'gutenberg';
}

$restore_content = $show_builder_picker && (bool) $saved_builder;

$wrap_classes = array( 'lsep-get-started-content' );
if ( ! $show_builder_picker || $restore_content ) {
	$wrap_classes[] = 'is-content-active';
}
if ( ! $show_builder_picker ) {
	$wrap_classes[] = 'lsep-gs-no-picker';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $wrap_classes ) ); ?>" id="lsep-gs-wrap" data-default-builder="<?php echo esc_attr( $default_builder ); ?>">
	<?php if ( $show_builder_picker ) : ?>
	<div class="lsep-gs-builder-section" id="lsep-gs-builder-section">
		<div class="lsep-gs-choose-heading">
			<h2><?php esc_html_e( 'Choose your builder', 'language-switcher-for-elementor-polylang' ); ?></h2>
			<p><?php esc_html_e( 'Select the builder you use to add and manage the language switcher.', 'language-switcher-for-elementor-polylang' ); ?></p>
		</div>

		<div class="lsep-gs-builder-cards">
			<?php if ( $has_elementor ) : ?>
			<button type="button" class="lsep-gs-builder-card<?php echo 'elementor' === $default_builder ? ' is-selected' : ''; ?>" data-builder="elementor">
				<img class="lsep-gs-builder-icon" src="<?php echo esc_url( $assets_url . 'elementor-icon.png' ); ?>" alt="" />
				<span class="lsep-gs-builder-text">
					<span class="lsep-gs-builder-title"><?php esc_html_e( 'Elementor', 'language-switcher-for-elementor-polylang' ); ?></span>
					<span class="lsep-gs-builder-desc"><?php esc_html_e( 'Use the widget to add the language switcher.', 'language-switcher-for-elementor-polylang' ); ?></span>
				</span>
				<span class="dashicons dashicons-arrow-right-alt2 lsep-gs-chevron" aria-hidden="true"></span>
			</button>
			<?php endif; ?>

			<button type="button" class="lsep-gs-builder-card<?php echo 'gutenberg' === $default_builder ? ' is-selected' : ''; ?>" data-builder="gutenberg">
				<img class="lsep-gs-builder-icon" src="<?php echo esc_url( $assets_url . 'gutenberg-icon.png' ); ?>" alt="" />
				<span class="lsep-gs-builder-text">
					<span class="lsep-gs-builder-title"><?php esc_html_e( 'Gutenberg', 'language-switcher-for-elementor-polylang' ); ?></span>
					<span class="lsep-gs-builder-desc"><?php esc_html_e( 'Add the language switcher using a block.', 'language-switcher-for-elementor-polylang' ); ?></span>
				</span>
				<span class="dashicons dashicons-arrow-right-alt2 lsep-gs-chevron" aria-hidden="true"></span>
			</button>

			<?php if ( $has_divi ) : ?>
			<button type="button" class="lsep-gs-builder-card<?php echo 'divi' === $default_builder ? ' is-selected' : ''; ?>" data-builder="divi">
				<img class="lsep-gs-builder-icon" src="<?php echo esc_url( $assets_url . 'divi-icon.png' ); ?>" alt="" />
				<span class="lsep-gs-builder-text">
					<span class="lsep-gs-builder-title"><?php esc_html_e( 'Divi', 'language-switcher-for-elementor-polylang' ); ?></span>
					<span class="lsep-gs-builder-desc"><?php esc_html_e( 'Use the Divi module to display the switcher.', 'language-switcher-for-elementor-polylang' ); ?></span>
				</span>
				<span class="dashicons dashicons-arrow-right-alt2 lsep-gs-chevron" aria-hidden="true"></span>
			</button>
			<?php endif; ?>
		</div>
	</div>

	<button type="button" class="lsep-gs-back-btn" id="lsep-gs-back-btn">
		<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
		<?php esc_html_e( 'Change builder', 'language-switcher-for-elementor-polylang' ); ?>
	</button>
	<?php endif; ?>

	<?php
	$guide_titles = array(
		'elementor' => __( 'Elementor Quick Start Guide', 'language-switcher-for-elementor-polylang' ),
		'gutenberg' => __( 'Gutenberg Quick Start Guide', 'language-switcher-for-elementor-polylang' ),
		'divi'      => __( 'Divi Quick Start Guide', 'language-switcher-for-elementor-polylang' ),
	);
	$guide_subs   = array(
		'elementor' => __( 'Follow these simple steps to add and configure the Language Switcher widget in Elementor.', 'language-switcher-for-elementor-polylang' ),
		'gutenberg' => __( 'Follow these simple steps to add and configure the Language Switcher block in the Block Editor.', 'language-switcher-for-elementor-polylang' ),
		'divi'      => __( 'Follow these simple steps to add and configure the Language Switcher module in Divi.', 'language-switcher-for-elementor-polylang' ),
	);
	$initial_title = isset( $guide_titles[ $default_builder ] ) ? $guide_titles[ $default_builder ] : $guide_titles['gutenberg'];
	$initial_sub   = isset( $guide_subs[ $default_builder ] ) ? $guide_subs[ $default_builder ] : $guide_subs['gutenberg'];
	?>
	<div class="lsep-gs-info-grid" id="lsep-gs-info-grid">
		<div class="lsep-gs-info-card lsep-gs-guide-card">
			<h2 id="lsep-gs-guide-title"><?php echo esc_html( $initial_title ); ?></h2>
			<p class="lsep-gs-sub" id="lsep-gs-guide-sub"><?php echo esc_html( $initial_sub ); ?></p>
			<div id="lsep-gs-steps"></div>
		</div>

		<div class="lsep-gs-info-card lsep-gs-video-card">
			<header class="lsep-gs-video-header">
				<h2 class="lsep-gs-video-title"><?php esc_html_e( 'Video Tutorial', 'language-switcher-for-elementor-polylang' ); ?></h2>
			</header>
			<div class="lsep-video-container">
				<iframe
					id="lsep-gs-video-iframe"
					width="100%"
					height="380"
					src="<?php echo esc_url( 'https://www.youtube.com/embed/' . $video_id ); ?>"
					title="<?php echo esc_attr__( 'Language Switcher for Elementor & Polylang Tutorial', 'language-switcher-for-elementor-polylang' ); ?>"
					frameborder="0"
					allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
					allowfullscreen>
				</iframe>
			</div>
		</div>
	</div>

	<footer class="lsep-gs-footer">

	    <?php lsep_render_autopoly_promo( 'get_started' ); ?>
		
		<div class="lsep-gs-footer-card">
			<div class="lsep-gs-footer-icon" aria-hidden="true">
				<span class="dashicons dashicons-editor-help"></span>
			</div>
			<h3><?php esc_html_e( 'Support', 'language-switcher-for-elementor-polylang' ); ?></h3>
			<p><?php esc_html_e( 'Need help? Our team can assist with setup and troubleshooting.', 'language-switcher-for-elementor-polylang' ); ?></p>
			<div class="lsep-gs-footer-links">
				<a
					class="lsep-gs-footer-btn"
					href="<?php echo esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/#new-topic-0' ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'Get Support', 'language-switcher-for-elementor-polylang' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
				</a>
			</div>
		</div>

		<div class="lsep-gs-footer-card">
			<div class="lsep-gs-footer-icon" aria-hidden="true">
				<span class="dashicons dashicons-star-filled"></span>
			</div>
			<h3><?php esc_html_e( 'Your Feedback Matters', 'language-switcher-for-elementor-polylang' ); ?></h3>
			<p><?php esc_html_e( 'If you\'re happy with the plugin, we\'d greatly appreciate a quick review. Your support helps us continue improving it.', 'language-switcher-for-elementor-polylang' ); ?></p>
			<div class="lsep-gs-footer-links">
				<a
					class="lsep-gs-footer-btn"
					href="<?php echo esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/reviews/#new-post' ); ?>"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php esc_html_e( 'Leave a Review', 'language-switcher-for-elementor-polylang' ); ?>
					<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
				</a>
			</div>
		</div>
	</footer>
</div>
