<?php

if ( ! class_exists( 'LSEPFeedbackNotice' ) ) {
	/**
	 * Class for feedback notice.
	 */
	class LSEPFeedbackNotice {
		/**
		 * The Constructor
		 */
		public function __construct() {
			// register actions.

			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'admin_notice_for_reviews' ) );
				add_action( 'wp_ajax_lsep_dismiss_notice', array( $this, 'lsep_dismiss_review_notice' ) );
			}
		}
		/**
		 *  Ajax callback for review notice.
		 */
		public function lsep_dismiss_review_notice() {
			$rs = update_option( 'lsep-ratingDiv', 'yes' );
			echo json_encode( array( 'success' => 'true' ) );
			exit;
		}
		/**
		 * Admin notice.
		 */
		public function admin_notice_for_reviews() {

			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}
			 // Get installation dates and rated settings.
			 $installation_date = get_option( 'lsep-installDate' );
			 $already_rated     = get_option( 'lsep-ratingDiv' ) != false ? get_option( 'lsep-ratingDiv' ) : 'no';

			 // Check user already rated.
			if ( 'yes' === $already_rated ) {
				return;
			}

			// Grab plugin installation date and compare it with current date.
			$display_date = gmdate( 'Y-m-d h:i:s' );
			$install_date = new DateTime( $installation_date );
			$current_date = new DateTime( $display_date );
			$difference   = $install_date->diff( $current_date );
			$diff_days    = $difference->days;

			// Check if installation days is greator then week.
			if ( isset( $diff_days ) && $diff_days >= 3 ) {
				echo $this->create_notice_content();
			}
		}
		/**
		 * Generated review notice HTML.
		 */
		public function create_notice_content() {
			$ajax_url           = admin_url( 'admin-ajax.php' );
			$ajax_callback      = 'lsep_dismiss_notice';
			$wrap_cls           = 'notice notice-info is-dismissible';
			$img_path           = LSEP_PLUGIN_URL . 'assets/images/lsep-icon.png';
			$p_name             = 'Language Switcher for Elementor & Polylang';
			$like_it_text       = 'Rate Now! ★★★★★';
			$already_rated_text = esc_html__( 'I already rated it', 'LSEP' );
			$not_interested     = esc_html__( 'Not Interested', 'LSEP' );
			$not_like_it_text   = esc_html__( 'No, not good enough, i do not like to rate it!', 'LSEP' );
			$p_link             = esc_url( 'https://wordpress.org/support/plugin/language-switcher-for-elementor-polylang/reviews/#new-post' );
			$pro_url            = esc_url( 'https://1.envato.market/calendar' );

			$message = "Thanks for using <b>$p_name</b>. We hope it meets your expectations! <br/>Please give us a quick rating, it works as a boost for us to keep working on more <a href='https://coolplugins.net/?utm_source=plugin_dashboard&utm_medium=reviewbox' target='_blank'><strong>Cool Plugins</strong></a>!<br/>";

			$html       = '<div data-ajax-url="%8$s"  data-ajax-callback="%9$s" class="lsep-feedback-notice-wrapper %1$s">
        <div class="logo_container"><a href="%5$s"><img src="%2$s" alt="%3$s"></a></div>
        <div class="message_container">%4$s
        <div class="callto_action">
        <ul>
            <li class="love_it"><a href="%5$s" class="like_it_btn button button-primary" target="_new" title="%6$s">%6$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button lsep_dismiss_notice" title="%7$s">%7$s</a></li>
            <li class="already_rated"><a href="javascript:void(0);" class="already_rated_btn button lsep_dismiss_notice" title="%11$s">%11$s</a></li>
           
        </ul>
        <div class="clrfix"></div>
        </div>
        </div>
        </div>';
			$inline_css = '<style>.lsep-feedback-notice-wrapper.notice.notice-info.is-dismissible {
            padding: 5px;
            display: table;
            width: 100%;
            max-width: 820px;
            clear: both;
            border-radius: 5px;
            border: 2px solid #b7bfc7;
        }
        .lsep-feedback-notice-wrapper .logo_container {
            width: 100px;
            display: table-cell;
            padding: 5px;
            vertical-align: middle;
        }
        .lsep-feedback-notice-wrapper .logo_container a,
        .lsep-feedback-notice-wrapper .logo_container img {
            width:100%;
            height:auto;
            display:inline-block;
        }
        .lsep-feedback-notice-wrapper .message_container {
            display: table-cell;
            padding: 5px 20px 5px 5px;
            vertical-align: middle;
        }
        .lsep-feedback-notice-wrapper ul li {
            float: left;
            margin: 0px 10px 0 0;
        }
        .lsep-feedback-notice-wrapper ul li.already_rated a:before {
            color: #f12945;
            content: "\f153";
            font: normal 18px/22px dashicons;
            display: inline-block;
            vertical-align: middle;
            margin-right: 3px;
        }
        .lsep-feedback-notice-wrapper ul li .button-primary {
            background: #008bff;
        }
        .lsep-feedback-notice-wrapper ul li .button-primary:hover {
            background: #0f1031;
            border-color: transparent;
        }
        .lsep-feedback-notice-wrapper a {
            color: #008bff;
        }
        
        /* This css is for license registration page */
        .lsep-notice-red.uninstall {
            max-width: 700px;
            display: block;
            padding: 8px;
            border: 2px solid #157d0f;
            margin: 10px 0;
            background: #13a50b;
            font-weight: bold;
            font-size: 13px;
            color: #ffffff;
        }
        .clrfix{
            clear:both;
        }</style>';
			$inline_js  = "<script>jQuery(document).ready(function ($) {
            $('.lsep_dismiss_notice').on('click', function (event) {
                var thisE = $(this);
                var wrapper=thisE.parents('.lsep-feedback-notice-wrapper');
                var ajaxURL=wrapper.data('ajax-url');
                var ajaxCallback=wrapper.data('ajax-callback');
                $.post(ajaxURL, { 'action':ajaxCallback }, function( data ) {
                    wrapper.slideUp('fast');
                  }, 'json');
            });
        });</script>";
			$output     = sprintf(
				$html,
				$wrap_cls,
				$img_path,
				$p_name,
				$message,
				$p_link,
				$like_it_text,
				$already_rated_text,
				$ajax_url, // 8
				$ajax_callback, // 9
				$pro_url, // 10
				$not_interested
			);
			$output    .= $inline_css . ' ' . $inline_js;
			return $output;
		}

	} //class end

}
new LSEPFeedbackNotice();


