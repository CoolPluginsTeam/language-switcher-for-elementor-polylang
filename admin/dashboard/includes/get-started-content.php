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
$video_url  = 'https://www.youtube.com/watch?v=' . $video_id;
?>
<div class="lsep-get-started-content" id="lsep-gs-wrap">
	<div class="lsep-gs-builder-section" id="lsep-gs-builder-section">
		<div class="lsep-gs-choose-heading">
			<h2><?php esc_html_e( 'Choose your builder', 'language-switcher-for-elementor-polylang' ); ?></h2>
			<p><?php esc_html_e( 'Select the builder you use to add and manage the language switcher.', 'language-switcher-for-elementor-polylang' ); ?></p>
		</div>

		<div class="lsep-gs-builder-cards">
			<button type="button" class="lsep-gs-builder-card is-selected" data-builder="elementor">
				<img class="lsep-gs-builder-icon" src="<?php echo esc_url( $assets_url . 'elementor-icon.png' ); ?>" alt="" />
				<span class="lsep-gs-builder-text">
					<span class="lsep-gs-builder-title"><?php esc_html_e( 'Elementor', 'language-switcher-for-elementor-polylang' ); ?></span>
					<span class="lsep-gs-builder-desc"><?php esc_html_e( 'Use the widget to add the language switcher.', 'language-switcher-for-elementor-polylang' ); ?></span>
				</span>
				<span class="dashicons dashicons-arrow-right-alt2 lsep-gs-chevron" aria-hidden="true"></span>
			</button>

			<button type="button" class="lsep-gs-builder-card" data-builder="gutenberg">
				<img class="lsep-gs-builder-icon" src="<?php echo esc_url( $assets_url . 'gutenberg-icon.png' ); ?>" alt="" />
				<span class="lsep-gs-builder-text">
					<span class="lsep-gs-builder-title"><?php esc_html_e( 'Gutenberg', 'language-switcher-for-elementor-polylang' ); ?></span>
					<span class="lsep-gs-builder-desc"><?php esc_html_e( 'Add the language switcher using a block.', 'language-switcher-for-elementor-polylang' ); ?></span>
				</span>
				<span class="dashicons dashicons-arrow-right-alt2 lsep-gs-chevron" aria-hidden="true"></span>
			</button>

			<button type="button" class="lsep-gs-builder-card" data-builder="divi">
				<img class="lsep-gs-builder-icon" src="<?php echo esc_url( $assets_url . 'divi-icon.png' ); ?>" alt="" />
				<span class="lsep-gs-builder-text">
					<span class="lsep-gs-builder-title"><?php esc_html_e( 'Divi', 'language-switcher-for-elementor-polylang' ); ?></span>
					<span class="lsep-gs-builder-desc"><?php esc_html_e( 'Use the Divi module to display the switcher.', 'language-switcher-for-elementor-polylang' ); ?></span>
				</span>
				<span class="dashicons dashicons-arrow-right-alt2 lsep-gs-chevron" aria-hidden="true"></span>
			</button>
		</div>
	</div>

	<button type="button" class="lsep-gs-back-btn" id="lsep-gs-back-btn">
		<span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
		<?php esc_html_e( 'Change builder', 'language-switcher-for-elementor-polylang' ); ?>
	</button>

	<div class="lsep-gs-info-grid" id="lsep-gs-info-grid">
		<div class="lsep-gs-info-card lsep-gs-guide-card">
			<h2 id="lsep-gs-guide-title"><?php esc_html_e( 'Elementor Quick Start Guide', 'language-switcher-for-elementor-polylang' ); ?></h2>
			<p class="lsep-gs-sub" id="lsep-gs-guide-sub"><?php esc_html_e( 'Follow these simple steps to add and configure the Language Switcher widget in Elementor.', 'language-switcher-for-elementor-polylang' ); ?></p>
			<div id="lsep-gs-steps"></div>
		</div>

		<div class="lsep-gs-info-card lsep-gs-video-card">
			<header class="lsep-gs-video-header">
				<h2 class="lsep-gs-video-title"><?php esc_html_e( 'Video Tutorial', 'language-switcher-for-elementor-polylang' ); ?></h2>
			</header>
			<div class="lsep-gs-video-body">
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
				<a
					id="lsep-gs-video-cta"
					href="<?php echo esc_url( $video_url ); ?>"
					class="button button-secondary lsep-gs-video-cta"
					target="_blank"
					rel="noopener noreferrer"
				>
					<span class="lsep-gs-video-cta-icon" aria-hidden="true"></span>
					<span class="lsep-gs-video-cta-label">
						<?php esc_html_e( 'Watch on YouTube', 'language-switcher-for-elementor-polylang' ); ?>
					</span>
				</a>
			</div>
		</div>
	</div>
</div>
