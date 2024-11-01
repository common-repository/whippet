<?php
/**
 * Whippet Functions
 *
 * @category Whippet
 * @package  Whippet
 * @author   Jake Henshall
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.hashbangcode.com/
 */

namespace Whippet;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whippet Functions Class
 *
 * @category Whippet
 * @package  Whippet
 * @author   Jake Henshall
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://www.hashbangcode.com/
 */
class Functions {

	private static $comment_pages = array(
		'comment.php',
		'edit-comments.php',
		'moderation.php',
		'options-discussion.php',
	);

	public function __construct() {
		$whippet_options = get_option( 'whippet_options' );

		/**
		 * Options Actions + Filters
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['disable_emojis'] ) && $whippet_options['disable_emojis'] === '1' ) {
			add_action( 'init', array( $this, 'whippet_disable_emojis' ) );
		}

		if ( ! empty( $whippet_options['disable_embeds'] ) && $whippet_options['disable_embeds'] === '1' ) {
			add_action( 'init', array( $this, 'whippet_disable_embeds' ), 9999 );
		}

		if ( ! empty( $whippet_options['remove_query_strings'] ) && $whippet_options['remove_query_strings'] === '1' ) {
			add_action( 'init', array( $this, 'whippet_remove_query_strings' ) );
		}

		/**
		 * Disable XML-RPC
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['disable_xmlrpc'] ) && $whippet_options['disable_xmlrpc'] === '1' ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'wp_headers', array( $this, 'whippet_remove_x_pingback' ) );
			add_filter( 'pings_open', '__return_false', 9999 );
		}

		if ( ! empty( $whippet_options['remove_jquery_migrate'] ) && $whippet_options['remove_jquery_migrate'] === '1' ) {
			add_filter( 'wp_default_scripts', array( $this, 'whippet_remove_jquery_migrate' ) );
		}

		if ( ! empty( $whippet_options['hide_wp_version'] ) && $whippet_options['hide_wp_version'] === '1' ) {
			remove_action( 'wp_head', array( $this, 'wp_generator' ) );
			add_filter( 'the_generator', array( $this, 'whippet_hide_wp_version' ) );
		}

		if ( ! empty( $whippet_options['remove_wlwmanifest_link'] ) && $whippet_options['remove_wlwmanifest_link'] === '1' ) {
			remove_action( 'wp_head', array( $this, 'wlwmanifest_link' ) );
		}

		if ( ! empty( $whippet_options['remove_rsd_link'] ) && $whippet_options['remove_rsd_link'] === '1' ) {
			remove_action( 'wp_head', array( $this, 'rsd_link' ) );
		}

		/**
		 * Remove Shortlink
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['remove_shortlink'] ) && $whippet_options['remove_shortlink'] === '1' ) {
			remove_action( 'wp_head', array( $this, 'wp_shortlink_wp_head' ) );
			remove_action( 'template_redirect', array( $this, 'wp_shortlink_header' ), 11, 0 );
		}

		if ( ! empty( $whippet_options['disable_rss_feeds'] ) && $whippet_options['disable_rss_feeds'] === '1' ) {
			add_action( 'do_feed', array( $this, 'whippet_disable_rss_feeds' ), 1 );
			add_action( 'do_feed_rdf', array( $this, 'whippet_disable_rss_feeds' ), 1 );
			add_action( 'do_feed_rss', array( $this, 'whippet_disable_rss_feeds' ), 1 );
			add_action( 'do_feed_rss2', array( $this, 'whippet_disable_rss_feeds' ), 1 );
			add_action( 'do_feed_atom', array( $this, 'whippet_disable_rss_feeds' ), 1 );
			add_action( 'do_feed_rss2_comments', array( $this, 'whippet_disable_rss_feeds' ), 1 );
			add_action( 'do_feed_atom_comments', array( $this, 'whippet_disable_rss_feeds' ), 1 );
		}

		if ( ! empty( $whippet_options['remove_feed_links'] ) && $whippet_options['remove_feed_links'] === '1' ) {
			remove_action( 'wp_head', array( $this, 'feed_links' ), 2 );
			remove_action( 'wp_head', array( $this, 'feed_links_extra' ), 3 );
		}

		if ( ! empty( $whippet_options['disable_self_pingbacks'] ) && $whippet_options['disable_self_pingbacks'] === '1' ) {
			add_action( 'pre_ping', array( $this, 'whippet_disable_self_pingbacks' ) );
		}

		/**
		 * Remove REST API Links
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['remove_rest_api_links'] ) && $whippet_options['remove_rest_api_links'] === '1' ) {
			remove_action( 'wp_head', array( $this, 'rest_output_link_wp_head' ) );
			remove_action( 'template_redirect', array( $this, 'rest_output_link_header' ), 11, 0 );
		}

		/**
		 * Disable Google Maps
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['disable_google_maps'] ) && $whippet_options['disable_google_maps'] === '1' ) {
			add_action( 'wp_loaded', array( $this, 'whippet_disable_google_maps' ) );
		}

		/**
		 * Disable WooCommerce Scripts
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['disable_woocommerce_scripts'] ) && $whippet_options['disable_woocommerce_scripts'] === '1' ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'whippet_disable_woocommerce_scripts' ), 99 );
		}

		/**
		 * Disable WooCommerce Cart Fragmentation
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['disable_woocommerce_cart_fragmentation'] ) && $whippet_options['disable_woocommerce_cart_fragmentation'] === '1' ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'whippet_disable_woocommerce_cart_fragmentation' ), 99 );
		}

		/**
		 * Disable WooCommerce Status Meta Box
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['disable_woocommerce_status'] ) && $whippet_options['disable_woocommerce_status'] === '1' ) {
			add_action( 'wp_dashboard_setup', array( $this, 'whippet_disable_woocommerce_status' ) );
		}

		/**
		 * Disable WooCommerce Widgets
		 *
		 * @var [type]
		 */
		if ( ! empty( $whippet_options['disable_woocommerce_widgets'] ) && $whippet_options['disable_woocommerce_widgets'] === '1' ) {
			add_action( 'widgets_init', array( $this, 'whippet_disable_woocommerce_widgets' ), 99 );
		}

		if ( ! empty( $whippet_options['disable_heartbeat'] ) ) {
			add_action( 'init', array( $this, 'whippet_disable_heartbeat' ), 1 );
		}

		if ( ! empty( $whippet_options['heartbeat_frequency'] ) ) {
			add_filter( 'heartbeat_settings', array( $this, 'whippet_heartbeat_frequency' ) );
		}

		if ( ! empty( $whippet_options['limit_post_revisions'] ) ) {
			define( 'WP_POST_REVISIONS', $whippet_options['limit_post_revisions'] );
		}

		if ( ! empty( $whippet_options['autosave_interval'] ) ) {
			define( 'AUTOSAVE_INTERVAL', $whippet_options['autosave_interval'] );
		}

		if ( ! empty( $whippet_options['disable_admin_bar'] ) && $whippet_options['disable_admin_bar'] === '1' ) {
			add_filter( 'show_admin_bar', '__return_false' );
			add_action( 'admin_print_scripts-profile.php', array( $this, 'whippet_disable_admin_bar' ) );
		}

		if ( ! empty( $whippet_options['remove_comments'] ) && $whippet_options['remove_comments'] === '1' ) {
			// Remove update check.
			add_filter( 'the_posts', array( $this, 'set_comment_status' ) );
			add_filter( 'comments_open', array( $this, 'close_comments' ), 20, 2 );
			add_filter( 'pings_open', array( $this, 'close_comments' ), 20, 2 );
			add_action( 'admin_init', array( $this, 'remove_comments' ) );
			add_action( 'admin_menu', array( $this, 'remove_menu_items' ) );
			add_filter( 'add_menu_classes', array( $this, 'add_menu_classes' ) );
			// Remove items in dashboard.
			add_action( 'admin_footer-index.php', array( $this, 'remove_dashboard_comments_areas' ) );
			// Change admin bar items.
			add_action( 'admin_bar_menu', array( $this, 'remove_admin_bar_comment_items' ), 999 );
			add_action( 'admin_bar_menu', array( $this, 'remove_network_comment_items' ), 999 );
			// Replace the theme's or the core comments template with an empty one.
			add_filter( 'comments_template', array( $this, 'comments_template' ) );
			// Remove comment feed.
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			add_action( 'wp_head', array( $this, 'feed_links' ), 2 );
			add_action( 'wp_head', array( $this, 'feed_links_extra' ), 3 );
			add_action( 'template_redirect', array( $this, 'filter_query' ), 9 );
			add_filter( 'wp_headers', array( $this, 'filter_wp_headers' ) );
			// Remove default comment widget.
			add_action( 'widgets_init', array( $this, 'unregister_default_wp_widgets' ), 1 );
			// Remove comment options in profile page.
			add_action( 'personal_options', array( $this, 'remove_profile_items' ) );
			// Replace xmlrpc methods.
			add_filter( 'xmlrpc_methods', array( $this, 'xmlrpc_replace_methods' ) );
			// Set content of <wfw:commentRss> to empty string.
			add_filter( 'post_comments_feed_link', '__return_empty_string' );
			// Set content of <slash:comments> to empty string.
			add_filter( 'get_comments_number', '__return_empty_string' );
			// Return empty string for post comment link, which takes care of <comments>.
			add_filter( 'get_comments_link', '__return_empty_string' );
			// Remove comments popup.
			add_filter( 'query_vars', array( $this, 'filter_query_vars' ) );
			// Remove 'Discussion Settings' help tab from post edit screen.
			add_action( 'admin_head-post.php', array( $this, 'remove_help_tabs' ), 10, 3 );
			// Remove rewrite rules used for comment feed archives.
			add_filter( 'comments_rewrite_rules', '__return_empty_array', 99 );
			// Remove rewrite rules for the legacy comment feed and post type comment pages.
			add_filter( 'rewrite_rules_array', array( $this, 'filter_rewrite_rules_array' ), 99 );
			// Return an object with each comment stat set to zero.
			add_filter( 'wp_count_comments', array( $this, 'filter_count_comments' ) );

		}
	}

	/**
	 * Disable Emojis
	 *
	 * @return boolean [description]
	 */
	public static function whippet_disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'whippet_disable_emojis_tinymce' ) );
	}

	public static function whippet_disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}

	/**
	 * Disable Embeds
	 *
	 * @return [type] [description]
	 */
	public static function whippet_disable_embeds() {
		global $wp;
		$wp->public_query_vars = array_diff( $wp->public_query_vars, array( 'embed' ) );
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );
		add_filter( 'embed_oembed_discover', array( $this, '__return_false' ) );
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_filter( 'tiny_mce_plugins', array( $this, 'whippet_disable_embeds_tiny_mce_plugin' ) );
		add_filter( 'rewrite_rules_array', array( $this, 'whippet_disable_embeds_rewrites' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'disable_embeds_enqueue_block_editor_assets' ) );
		add_action( 'wp_default_scripts', array( $this, 'disable_embeds_remove_script_dependencies' ) );
		remove_filter( 'pre_oembed_result', array( $this, 'wp_filter_pre_oembed_result', 10 ) );
	}

	/**
	 * Disable Embeds Tiny MCE
	 *
	 * @param  [type] $plugins [description]
	 * @return [type]          [description]
	 */
	public static function whippet_disable_embeds_tiny_mce_plugin( $plugins ) {
		return array_diff( $plugins, array( 'wpembed' ) );
	}

	/**
	 * Disable Embeds Rewrites
	 *
	 * @param  [type] $rules [description]
	 * @return [type]        [description]
	 */
	public static function whippet_disable_embeds_rewrites( $rules ) {
		foreach ( $rules as $rule => $rewrite ) {
			if ( false !== strpos( $rewrite, 'embed=true' ) ) {
				unset( $rules[$rule] );
			}
		}
		return $rules;
	}

	/**
	 * Enqueues JavaScript for the block editor.
	 *
	 * @since 1.4.0
	 *
	 * This is used to unregister the `core-embed/wordpress` block type.
	 */
	public static function disable_embeds_enqueue_block_editor_assets() {
		wp_enqueue_script(
			'disable-embeds',
			plugins_url( 'js/editor.js', __FILE__ ),
			array(
				'wp-edit-post',
				'wp-editor',
				'wp-dom',
			),
			'20181202',
			true
		);
	}
	/**
	 * Removes wp-embed dependency of core packages.
	 *
	 * @since 1.4.0
	 *
	 * @param \WP_Scripts $scripts WP_Scripts instance, passed by reference.
	 */
	public static function disable_embeds_remove_script_dependencies( $scripts ) {
		if ( ! empty( $scripts->registered['wp-edit-post'] ) ) {
			$scripts->registered['wp-edit-post']->deps = array_diff(
				$scripts->registered['wp-edit-post']->deps,
				array( 'wp-embed' )
			);
		}
	}

	/**
	 * Disable XML-RPC
	 *
	 * @param  [type] $headers [description]
	 * @return [type]          [description]
	 */
	public static function whippet_remove_x_pingback( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	/**
	 * Disable Google Maps
	 *
	 * @return [type] [description]
	 */
	public static function whippet_disable_google_maps() {
		ob_start ( array( $this, 'whippet_disable_google_maps_regex' ) );
	}

	public static function whippet_disable_google_maps_regex( $html ) {
		$html = preg_replace( '/<script[^<>]*\/\/maps.(googleapis|google|gstatic).com\/[^<>]*><\/script>/i', '', $html );
		return $html;
	}

	/**
	 * Hide Nag Notices
	 *
	 * @return [type] [description]
	 */
	public static function hide_update_noticee_to_all_but_admin_users() {
		remove_all_actions( 'admin_notices' );
	}

	/**
	 * Disable WooCommerce Scripts
	 *
	 * @return [type] [description]
	 */
	public static function whippet_disable_woocommerce_scripts() {
		if ( function_exists( 'is_woocommerce' ) ) {
			if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
				global $whippet_options;

				// Dequeue WooCommerce Styles.
				wp_dequeue_style( 'woocommerce-general' );
				wp_dequeue_style( 'woocommerce-layout' );
				wp_dequeue_style( 'woocommerce-smallscreen' );
				wp_dequeue_style( 'woocommerce_frontend_styles' );
				wp_dequeue_style( 'woocommerce_fancybox_styles' );
				wp_dequeue_style( 'woocommerce_chosen_styles' );
				wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
				// Dequeue WooCommerce Scripts.
				wp_dequeue_script( 'wc_price_slider' );
				wp_dequeue_script( 'wc-single-product' );
				wp_dequeue_script( 'wc-add-to-cart' );
				wp_dequeue_script( 'wc-checkout' );
				wp_dequeue_script( 'wc-add-to-cart-variation' );
				wp_dequeue_script( 'wc-single-product' );
				wp_dequeue_script( 'wc-cart' );
				wp_dequeue_script( 'wc-chosen' );
				wp_dequeue_script( 'woocommerce' );
				wp_dequeue_script( 'prettyPhoto' );
				wp_dequeue_script( 'prettyPhoto-init' );
				wp_dequeue_script( 'jquery-blockui' );
				wp_dequeue_script( 'jquery-placeholder' );
				wp_dequeue_script( 'fancybox' );
				wp_dequeue_script( 'jqueryui' );

				if ( empty( $whippet_options['disable_woocommerce_cart_fragmentation']) || $whippet_options['disable_woocommerce_cart_fragmentation'] === '0' ) {
					wp_dequeue_script( 'wc-cart-fragments' );
				}
			}
		}
	}

	/**
	 * Disable WooCommerce Cart Fragmentation
	 *
	 * @return [type] [description]
	 */
	public static function whippet_disable_woocommerce_cart_fragmentation() {
		if ( function_exists( 'is_woocommerce' ) ) {
			wp_dequeue_script( 'wc-cart-fragments' );
		}
	}

	/**
	 * Disable WooCommerce Status
	 *
	 * @return [type] [description]
	 */
	public static function whippet_disable_woocommerce_status() {
		remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal' );
	}

	/**
	 * Disable WooCommerce Widgets
	 *
	 * @return [type] [description]
	 */
	public static function whippet_disable_woocommerce_widgets() {
		global $whippet_options;

		unregister_widget( 'WC_Widget_Products' );
		unregister_widget( 'WC_Widget_Product_Categories' );
		unregister_widget( 'WC_Widget_Product_Tag_Cloud' );
		unregister_widget( 'WC_Widget_Cart' );
		unregister_widget( 'WC_Widget_Layered_Nav' );
		unregister_widget( 'WC_Widget_Layered_Nav_Filters' );
		unregister_widget( 'WC_Widget_Price_Filter' );
		unregister_widget( 'WC_Widget_Product_Search' );
		unregister_widget( 'WC_Widget_Recently_Viewed' );

		if ( empty( $whippet_options['disable_woocommerce_reviews']) || $whippet_options['disable_woocommerce_reviews'] === '0' ) {
			unregister_widget( 'WC_Widget_Recent_Reviews' );
			unregister_widget( 'WC_Widget_Top_Rated_Products' );
			unregister_widget( 'WC_Widget_Rating_Filter' );
		}
	}

	/**
	 * Remove Query Strings
	 *
	 * @return [type] [description]
	 */
	public static function whippet_remove_query_strings() {
		if ( ! is_admin() ) {
			add_filter( 'script_loader_src', array( $this, 'whippet_remove_query_strings_split' ), 15 );
			add_filter( 'style_loader_src', array( $this, 'whippet_remove_query_strings_split' ), 15 );
		}
	}

	public static function whippet_remove_query_strings_split( $src ) {
		$output = preg_split( '/(&ver|\?ver)/', $src );
		return $output[0];
	}

	/**
	 * Remove jQuery Migrate
	 *
	 * @param  [type] $scripts [description]
	 * @return [type]          [description]
	 */
	public static function whippet_remove_jquery_migrate( $scripts ) {
		if ( ! is_admin() ) {
			$scripts->remove( 'jquery' );
			$scripts->add( 'jquery', false, array( 'jquery-core' ), '1.12.4' );
		}
	}

	/**
	 * Hide WordPress Version
	 *
	 * @return [type] [description]
	 */
	public static function whippet_hide_wp_version() {
		return '';
	}

	/**
	 * Disable RSS Feeds
	 *
	 * @return [type] [description]
	 */
	public static function whippet_disable_rss_feeds() {
		wp_die ( __( 'No feed available, please visit the <a href="' . esc_url( home_url('/') ) . '">homepage</a>!', 'whippet' ) );
	}

	/**
	 * Disable Self Pingbacks
	 *
	 * @param  [type] $links [description]
	 * @return [type]        [description]
	 */
	public static function whippet_disable_self_pingbacks( &$links ) {
		$home = get_option( 'home' );
		foreach ( $links as $l => $link ) {
			if ( strpos( $link, $home ) === 0 ) {
				unset( $links[ $l ] );
			}
		}
	}

	/**
	 * Disable Heartbeat
	 *
	 * @return [type] [description]
	 */
	public static function whippet_disable_heartbeat() {
		global $whippet_options;
		if ( ! empty( $whippet_options['disable_heartbeat'] ) ) {
			if ( $whippet_options['disable_heartbeat'] === 'disable_everywhere' ) {
				wp_deregister_script( 'heartbeat' );
			} elseif ( $whippet_options['disable_heartbeat'] === 'allow_posts' ) {
				global $pagenow;
				if ( $pagenow != 'post.php' && $pagenow != 'post-new.php' ) {
					wp_deregister_script( 'heartbeat' );
				}
			}
		}
	}

	/**
	 * Heartbeat Frequency
	 *
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public static function whippet_heartbeat_frequency( $settings ) {
		global $whippet_options;
		if ( ! empty( $whippet_options['heartbeat_frequency'] ) ) {
			$settings['interval'] = $whippet_options['heartbeat_frequency'];
		}
		return $settings;
	}

		/**
		 * Set the status on posts and pages - is_singular().
		 *
		 * @access public
		 * @since  0.0.1
		 * @uses   is_singular
		 *
		 * @param string $posts
		 *
		 * @return string $posts
		 */
		public function set_comment_status( $posts ) {
			if ( ! empty( $posts ) && is_singular() ) {
				$posts[ 0 ]->comment_status = 'closed';
				$posts[ 0 ]->ping_status    = 'closed';
			}
			return $posts;
		}
		/**
		 * Close comments, if open.
		 *
		 * @access public
		 * @since  0.0.1
		 *
		 * @param string|boolean $open
		 * @param string|integer $post_id
		 *
		 * @return bool|string $open
		 */
		public function close_comments( $open, $post_id ) {
			// If not open, than back.
			if ( ! $open ) {
				return $open;
			}
			$post = get_post( $post_id );
			// For all post types.
			if ( $post->post_type ) {
				return FALSE;
			} // 'closed' don`t work; @see http://codex.wordpress.org/Option_Reference#Discussion
			return $open;
		}
		/**
		 * Return default closed comment status.
		 *
		 * @since  04/08/2013
		 * @return string
		 */
		public function return_closed() {
			return 'closed';
		}
		/**
		 * Change options for don't use comments.
		 *
		 * Remove meta boxes on edit pages.
		 * Remove support on all post types for comments.
		 * Remove menu-entries.
		 * Disallow comments pages direct access.
		 *
		 * @access public
		 * @since  0.0.1
		 * @return void
		 */
		public function remove_comments() {
			global $pagenow;
			// For integer values.
			foreach ( array( 'comments_notify', 'default_pingback_flag' ) as $option ) {
				add_filter( 'pre_option_' . $option, '__return_zero' );
			}
			// For string false.
			foreach ( array( 'default_comment_status', 'default_ping_status' ) as $option ) {
				add_filter( 'pre_option_' . $option, array( $this, 'return_closed' ) );
			}
			// For all post types.
			// As alternative define an array( 'post', 'page' ).
			foreach ( get_post_types() as $post_type ) {
				// Remove the comment status meta box.
				remove_meta_box( 'commentstatusdiv', $post_type, 'normal' );
				// Remove the trackbacks meta box.
				remove_meta_box( 'trackbacksdiv', $post_type, 'normal' );
				// Remove all comments/trackbacks from tables.
				if ( post_type_supports( $post_type, 'comments' ) ) {
					remove_post_type_support( $post_type, 'comments' );
					remove_post_type_support( $post_type, 'trackbacks' );
				}
			}
			// Filter for different pages.
			if ( in_array( $pagenow, self::$comment_pages, FALSE ) ) {
				wp_die(
					esc_html__( 'Comments are disabled on this site.', 'whippet' ),
					'',
					array( 'response' => 403 )
				);
				exit();
			}
			// Remove dashboard meta box for recents comments.
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		}
		/**
		 * Remove menu-entries.
		 *
		 * @access public
		 * @since  0.0.3
		 * @uses   remove_meta_box, remove_post_type_support
		 * @return void
		 */
		public function remove_menu_items() {
			remove_menu_page( 'edit-comments.php' );
			remove_submenu_page( 'options-general.php', 'options-discussion.php' );
		}
		/**
		 * Add class for last menu entry with no 20.
		 *
		 * @access  public
		 * @since   0.0.1
		 *
		 * @param array|string $menu
		 *
		 * @return array|string $menu
		 */
		public function add_menu_classes( $menu ) {
			if ( isset( $menu[ 20 ][ 4 ] ) ) {
				$menu[ 20 ][ 4 ] .= ' menu-top-last';
			}
			return $menu;
		}
		/**
		 * Remove comment related elements from the admin dashboard via JS.
		 *
		 * @access  public
		 * @since   0.0.1
		 * $return  string with js
		 */
		public function remove_dashboard_comments_areas() {
			?>
			<script type="text/javascript">
				//<![CDATA[
				jQuery( document ).ready( function( $ ) {
					// Welcome screen
					$( '.welcome-comments' ).parent().remove();
					// 'Right Now' dashboard widget
					$( 'div.table_discussion:first' ).remove();
					// 'Right Now' dashbaord widget since WP version 3.8, second ID since WP 4.0
					$( 'div#dash-right-now, #dashboard_right_now' ).find( '.comment-count' ).remove();
					// 'Activity' dashboard widget, since WP version 3.8
					$( 'div#dashboard_activity' ).find( '#latest-comments' ).remove();
				} );
				//]]>
			</script>
			<?php
		}
		/**
		 * Remove comment entry in Admin Bar.
		 *
		 * @access  public
		 * @since   0.0.1
		 *
		 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
		 *
		 * @return null
		 */
		public function remove_admin_bar_comment_items( $wp_admin_bar ) {
			if ( ! is_admin_bar_showing() ) {
				return NULL;
			}
			// Remove comment item in blog list for "My Sites" in Admin Bar.
			if ( isset( $GLOBALS[ 'blog_id' ] ) ) {
				$wp_admin_bar->remove_node( 'blog-' . $GLOBALS[ 'blog_id' ] . '-c' );
			}
			// Remove entry in admin bar.
			$wp_admin_bar->remove_node( 'comments' );
		}
		/**
		 * Remove comments item on network admin bar.
		 *
		 * @since    04/08/2013
		 * @internal param Array $wp_admin_bar
		 * @return void
		 */
		public function remove_network_comment_items() {
			if ( ! is_admin_bar_showing() ) {
				return NULL;
			}
			global $wp_admin_bar;
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				foreach ( (array) $wp_admin_bar->user->blogs as $blog ) {
					$wp_admin_bar->remove_node( 'blog-' . $blog->userblog_id . '-c' );
				}
			}
		}
		/**
		 * Display the links to the general feeds, without comments.
		 *
		 * @access public
		 * @since  0.0.4
		 * @uses   current_theme_supports, wp_parse_args, feed_content_type, get_bloginfo, esc_attr, get_feed_link, _x, __
		 *
		 * @param  array $args Optional arguments
		 *
		 * @return string
		 */
		public function feed_links( $args ) {
			if ( ! current_theme_supports( 'automatic-feed-links' ) ) {
				return NULL;
			}
			$defaults = array(
				// Translators: Separator between blog name and feed type in feed links.
				'separator' => _x(
					'&raquo;',
					'feed link',
					'whippet'
				),
				// Translators: 1: blog title, 2: separator (raquo).
				'feedtitle' => __( '%1$s %2$s Feed', 'whippet' ),
			);
			$args = wp_parse_args( $args, $defaults );
			echo '<link rel="alternate" type="' . esc_attr( feed_content_type() ) . '" title="' .
				esc_attr(
					sprintf(
						$args[ 'feedtitle' ],
						get_bloginfo( 'name' ),
						$args[ 'separator' ]
					)
				) . '" href="' . esc_attr( get_feed_link() ) . '"/>' . "\n";
		}
		/**
		 * Display the links to the extra feeds such as category feeds.
		 *
		 * Copy from WP default, but without comment feed; no filter available.
		 *
		 * @since 04/08/2013
		 *
		 * @param array $args Optional argument.
		 */
		public function feed_links_extra( $args ) {
			$defaults = array(
				/* Translators: Separator between blog name and feed type in feed links. */
				'separator'     => _x( '&raquo;', 'feed link', 'whippet' ),
				/* Translators: 1: blog name, 2: separator(raquo), 3: category name. */
				'cattitle'      => __( '%1$s %2$s %3$s Category Feed', 'whippet' ),
				/* Translators: 1: blog name, 2: separator(raquo), 3: tag name. */
				'tagtitle'      => __( '%1$s %2$s %3$s Tag Feed', 'whippet' ),
				/* Translators: 1: blog name, 2: separator(raquo), 3: author name.  */
				'authortitle'   => __( '%1$s %2$s Posts by %3$s Feed', 'whippet' ),
				/* Translators: 1: blog name, 2: separator(raquo), 3: search phrase. */
				'searchtitle'   => __( '%1$s %2$s Search Results for &#8220;%3$s&#8221; Feed', 'whippet' ),
				/* Translators: 1: blog name, 2: separator(raquo), 3: post type name. */
				'posttypetitle' => __( '%1$s %2$s %3$s Feed', 'whippet' ),
			);
			$args = wp_parse_args( $args, $defaults );
			if ( is_category() ) {
				$term = get_queried_object();
				$title = sprintf( $args[ 'cattitle' ], get_bloginfo( 'name' ), $args[ 'separator' ], $term->name );
				$href  = get_category_feed_link( $term->term_id );
			} elseif ( is_tag() ) {
				$term = get_queried_object();
				$title = sprintf( $args[ 'tagtitle' ], get_bloginfo( 'name' ), $args[ 'separator' ], $term->name );
				$href  = get_tag_feed_link( $term->term_id );
			} elseif ( is_author() ) {
				$author_id = (int) get_query_var( 'author' );
				$title = sprintf(
					$args[ 'authortitle' ], get_bloginfo( 'name' ), $args[ 'separator' ],
					get_the_author_meta( 'display_name', $author_id )
				);
				$href  = get_author_feed_link( $author_id );
			} elseif ( is_search() ) {
				$title = sprintf(
					$args[ 'searchtitle' ], get_bloginfo( 'name' ), $args[ 'separator' ], get_search_query( FALSE )
				);
				$href  = get_search_feed_link();
			} elseif ( is_post_type_archive() ) {
				$title = sprintf(
					$args[ 'posttypetitle' ], get_bloginfo( 'name' ), $args[ 'separator' ],
					post_type_archive_title( '', FALSE )
				);
				$href  = get_post_type_archive_feed_link( get_queried_object()->name );
			}
			if ( isset( $title, $href ) ) {
				echo '<link rel="alternate" type="' . esc_attr( feed_content_type() ) . '" title="' . esc_attr(
						$title
					) . '" href="' . esc_url( $href ) . '" />' . "\n";
			}
		}
		/**
		 * Redirect on comment feed, set status 301.
		 *
		 * @since  04/08/2013
		 * @return NULL
		 */
		public function filter_query() {
			if ( ! is_comment_feed() ) {
				return NULL;
			}
			if ( isset( $_GET[ 'feed' ] ) ) {
				wp_redirect( remove_query_arg( 'feed' ), 301 );
				exit();
			}
			// Redirect_canonical will do the rest.
			set_query_var( 'feed', '' );
		}
		/**
		 * Unset additional HTTP headers for pingback.
		 *
		 * @since   04/07/2013
		 *
		 * @param array $headers
		 *
		 * @return array $headers
		 */
		public function filter_wp_headers( $headers ) {
			unset( $headers[ 'X-Pingback' ] );
			return $headers;
		}
		/**
		 * Unregister default comment widget.
		 *
		 * @since   07/16/2012
		 */
		public function unregister_default_wp_widgets() {
			unregister_widget( 'WP_Widget_Recent_Comments' );
		}
		/**
		 * Remove options for Keyboard Shortcuts on profile page.
		 *
		 * @since  09/03/2012
		 *
		 * @return void
		 */
		public function remove_profile_items() {
			?>
			<script type="text/javascript">
				//<![CDATA[
				jQuery( document ).ready( function( $ ) {
					$( '#your-profile' ).find( '.form-table' ).first().find( 'tr:nth-child(3)' ).remove();
				} );
				//]]>
			</script>
			<?php
		}
		/**
		 * Replace the theme's or the core comments template with an empty one.
		 *
		 * @since  2016-02-16
		 * @return string The path to the empty template file.
		 */
		public function comments_template() {
			return plugin_dir_path( __FILE__ ) . 'comments.php';
		}
		/**
		 * Replace comment related XML_RPC methods.
		 *
		 * @access public
		 * @since  09/21/2013
		 *
		 * @param array $methods
		 *
		 * @return array $methods
		 */
		public function xmlrpc_replace_methods( $methods ) {
			$comment_methods = array(
				'wp.getCommentCount',
				'wp.getComment',
				'wp.getComments',
				'wp.deleteComment',
				'wp.editComment',
				'wp.newComment',
				'wp.getCommentStatusList',
			);
			foreach ( $comment_methods as $method_name ) {
				if ( isset( $methods[ $method_name ] ) ) {
					$methods[ $method_name ] = array( $this, 'xmlrpc_placeholder_method' );
				}
			}
			return $methods;
		}
		/**
		 * XML_RPC placeholder method.
		 *
		 * @access public
		 * @since  09/21/2013
		 * @return IXR_Error object
		 */
		public function xmlrpc_placeholder_method() {
			return new IXR_Error(
				403,
				esc_attr__( 'Comments are disabled on this site.', 'whippet' )
			);
		}
		/**
		 * Remove comments popup.
		 *
		 * @see    https://core.trac.wordpress.org/ticket/28617
		 *
		 * @since  12/14/2015
		 *
		 * @param  array $public_query_vars The array of whitelisted query variables.
		 *
		 * @return array
		 */
		public function filter_query_vars( $public_query_vars ) {
			$key = array_search( 'comments_popup', $public_query_vars, FALSE );
			if ( FALSE !== $key ) {
				unset( $public_query_vars[ $key ] );
			}
			return $public_query_vars;
		}
		/**
		 * Remove 'Discussion Settings' help tab from post edit screen.
		 *
		 * @since  01/01/2016
		 *
		 * @access private
		 */
		public function remove_help_tabs() {
			$current_screen = get_current_screen();
			if ( $current_screen->get_help_tab( 'discussion-settings' ) ) {
				$current_screen->remove_help_tab( 'discussion-settings' );
			}
		}
		/**
		 * Remove rewrite rules for the legacy comment feed and post type comment pages.
		 *
		 * @since  2016-02-16
		 *
		 * @param  array $rules The compiled array of rewrite rules.
		 *
		 * @return array The filtered array of rewrite rules.
		 */
		public function filter_rewrite_rules_array( $rules ) {
			if ( is_array( $rules ) ) {
				// Remove the legacy comment feed rule.
				foreach ( $rules as $k => $v ) {
					if ( FALSE !== strpos( $k, '|commentsrss2' ) ) {
						$new_k = str_replace( '|commentsrss2', '', $k );
						unset( $rules[ $k ] );
						$rules[ $new_k ] = $v;
					}
				}
				// Remove all other comment related rules.
				foreach ( $rules as $k => $v ) {
					if ( FALSE !== strpos( $k, 'comment-page-' ) ) {
						unset( $rules[ $k ] );
					}
				}
			}
			return $rules;
		}
		/**
		 * Return an object with each comment stat set to zero.
		 *
		 * Prevents 'wp_count_comments' form performing a database query.
		 *
		 * @since  2016-08-29
		 * @see    wp_count_comments
		 * @return object Comment stats.
		 */
		public function filter_count_comments() {
			return (object) array( 'approved'       => 0,
			                       'spam'           => 0,
			                       'trash'          => 0,
			                       'post-trashed'   => 0,
			                       'total_comments' => 0,
			                       'all'            => 0,
			                       'moderated'      => 0
			);
		}

}

new Functions();
