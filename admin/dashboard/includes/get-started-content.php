<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lsep-get-started-content">
	<div class="lsep-content-wrapper">
		<div class="lsep-content-left">
			<header class="lsep-gs-header">
				<h2 class="lsep-gs-title">
					<?php esc_html_e( 'Quick Start Guide', 'language-switcher-for-elementor-polylang' ); ?>
				</h2>
				<p class="lsep-gs-subtitle">
					<?php
					printf(
						/* translators: %s: plugin name. */
						esc_html__( 'Thanks for using %s.', 'language-switcher-for-elementor-polylang' ),
						'<strong>' . esc_html__( 'Language Switcher for Elementor & Polylang', 'language-switcher-for-elementor-polylang' ) . '</strong>'
					);
					?>
				</p>
				<p class="lsep-gs-subtitle">
					<?php esc_html_e( 'This plugin helps you create and manage your Elementor multilingual website with Polylang.', 'language-switcher-for-elementor-polylang' ); ?>
				</p>
			</header>

			<section class="lsep-gs-card lsep-gs-card--step">
				<div class="lsep-gs-card-header">
					<div class="lsep-gs-step-badge">1</div>
					<div class="lsep-gs-card-header-content">
						<h3 class="lsep-gs-card-title">
							<?php esc_html_e( 'Add Language Switcher Widget', 'language-switcher-for-elementor-polylang' ); ?>
						</h3>
					</div>
				</div>
				<ul class="lsep-gs-list">
					<li><?php esc_html_e( 'Open a page using Elementor.', 'language-switcher-for-elementor-polylang' ); ?></li>
					<li>
						<?php
						printf(
							/* translators: search keyword. */
							esc_html__( 'Search for %s in the widgets panel.', 'language-switcher-for-elementor-polylang' ),
							'<strong>"' . esc_html__( 'Language Switcher', 'language-switcher-for-elementor-polylang' ) . '"</strong>'
						);
						?>
					</li>
					<li><?php esc_html_e( 'Drag and drop the widget where you want to show the switcher.', 'language-switcher-for-elementor-polylang' ); ?></li>
					<li><?php esc_html_e( 'Customize the style, layout, and language display from the widget settings.', 'language-switcher-for-elementor-polylang' ); ?></li>
				</ul>
			</section>

			<section class="lsep-gs-card lsep-gs-card--step lsep-gs-card--muted">
				<div class="lsep-gs-card-header">
					<div class="lsep-gs-step-badge">2</div>
					<div class="lsep-gs-card-header-content">
						<h3 class="lsep-gs-card-title">
							<?php esc_html_e( 'Translate Elementor Templates', 'language-switcher-for-elementor-polylang' ); ?>
						</h3>
					</div>
				</div>
				<ul class="lsep-gs-list">
					<li>
						<?php
						printf(
							'%1$s <strong>%2$s</strong>',
							esc_html__( 'From your WordPress dashboard, go to', 'language-switcher-for-elementor-polylang' ),
							esc_html__( 'Templates > Saved Templates.', 'language-switcher-for-elementor-polylang' )
						);
						?>
					</li>
					<li><?php esc_html_e( 'Create a new template in Elementor.', 'language-switcher-for-elementor-polylang' ); ?></li>
					<li><?php esc_html_e( 'Translate your Elementor templates via Polylang.', 'language-switcher-for-elementor-polylang' ); ?></li>
				</ul>
				<div class="lsep-gs-actions">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=elementor_library' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Go to Template Settings', 'language-switcher-for-elementor-polylang' ); ?>
					</a>
				</div>
			</section>

			<section class="lsep-gs-card lsep-gs-card--step">
				<div class="lsep-gs-card-header">
					<div class="lsep-gs-step-badge">3</div>
					<div class="lsep-gs-card-header-content">
						<h3 class="lsep-gs-card-title">
							<?php esc_html_e( 'Translations Control Panel', 'language-switcher-for-elementor-polylang' ); ?>
						</h3>
					</div>
				</div>
				<ul class="lsep-gs-list">
					<li><?php esc_html_e( 'Manage and edit translated versions of your pages using the Translations Control Panel.', 'language-switcher-for-elementor-polylang' ); ?></li>
					<li><?php esc_html_e( 'Click the Edit icon to modify an existing translation.', 'language-switcher-for-elementor-polylang' ); ?></li>
					<li><?php esc_html_e( 'Click the Create icon to quickly start a translation.', 'language-switcher-for-elementor-polylang' ); ?></li>
				</ul>
			</section>
		</div>

		<div class="lsep-content-right">
			<section class="lsep-gs-card lsep-gs-card--video">
				<header class="lsep-gs-video-header">
					<h3 class="lsep-gs-video-title">
						<?php esc_html_e( 'Video Tutorial', 'language-switcher-for-elementor-polylang' ); ?>
					</h3>
				</header>
				<div class="lsep-gs-video-body">
					<div class="lsep-video-container">
						<iframe
							width="100%"
							height="260"
							src="https://www.youtube.com/embed/HyM0woo9Cg0"
							title="<?php echo esc_attr__( 'Language Switcher for Elementor & Polylang Tutorial', 'language-switcher-for-elementor-polylang' ); ?>"
							frameborder="0"
							allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
							allowfullscreen>
						</iframe>
					</div>
					<a
						href="https://www.youtube.com/watch?v=HyM0woo9Cg0"
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
			</section>
		</div>
	</div>
</div>
