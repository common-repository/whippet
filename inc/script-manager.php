<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whippet actual functionalty
 * ============================================================================
 */
class Whippet {
	/**
	 * Stores current content type
	 *
	 * @var string
	 */
	private $content_type = '';

	/**
	 * Stores entire entered by user selection
	 *
	 * @var [type]
	 */
	private $whippet_data = array();

	/**
	 * Stores list of all available assets (used in rendering panel)
	 *
	 * @var array
	 */
	private $collection = array();

	/**
	 * Initilize entire machine
	 */
	function __construct() {

		add_action( 'init', array( $this, 'load_configuration' ), 1 );

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'template_redirect', array( $this, 'detect_content_type' ) );

		if ( ! defined( 'WHIPPET_DISABLE_ON_FRONTEND' ) && ! is_admin() ) {
			add_action( 'wp_head', array( $this, 'collect_assets' ), 10000 );
			add_action( 'wp_footer', array( $this, 'collect_assets' ), 10000 );
			add_filter( 'script_loader_src', array( $this, 'unload_assets' ), 10, 2 );
			add_filter( 'style_loader_src', array( $this, 'unload_assets' ), 10, 2 );

			if ( ! defined( 'DISABLE_WHIPPET_PANEL' ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'append_asset' ) );
				add_action( 'wp_footer', array( $this, 'render_panel' ), 10000 + 1 );
			}
		}

		if ( defined( 'WHIPPET_ENABLE_ON_BACKEND' ) && is_admin() ) {
			add_action( 'admin_head', array( $this, 'collect_assets' ), 10000 );
			add_action( 'admin_footer', array( $this, 'collect_assets' ), 10000 );
			add_filter( 'script_loader_src', array( $this, 'unload_assets' ), 10, 2 );
			add_filter( 'style_loader_src', array( $this, 'unload_assets' ), 10, 2 );

			if ( ! defined( 'DISABLE_WHIPPET_PANEL' ) ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'append_asset' ) );
				add_action( 'admin_footer', array( $this, 'render_panel' ), 10000 + 1 );
			}
		}

		if ( ! defined( 'DISABLE_WHIPPET_PANEL' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'add_node_to_admin_bar' ), 1000 );
		}

		if ( ! defined( 'WHIPPET_DISABLE_ON_FRONTEND' ) ) {
			add_action( 'init', array( $this, 'update_configuration' ) );
		} elseif ( defined( 'WHIPPET_ENABLE_ON_BACKEND' ) ) {
			add_action( 'admin_init', array( $this, 'update_configuration' ) );
		}
	}

	private function get_visibility_asset( $type = '', $plugin = '' ) {
		$state = true;

		if ( isset( $this->gonzales_data['disabled'][ $type ][ $plugin ] ) ) {
			$state = false;

			if ( isset( $this->gonzales_data['enabled'][ $type ][ $plugin ][ $this->content_type ] ) ||
				isset( $this->gonzales_data['enabled'][ $type ][ $plugin ]['here'] ) ) {
				$state = true;
			}
		}

		return $state;
	}

	/**
	 * Check whether resource should be disabled or not.
	 *
	 * @param  string $url      Handler URL.
	 * @param  string $handle   Asset handle name.
	 * @return mixed
	 */
	public function unload_assets( $url, $handle ) {
		// WordPress 5.0.0.
		$polyfills_filter = 'wp-polyfill';

		if ( substr( $handle, 0, strlen( $polyfills_filter ) ) !== $polyfills_filter ) {
			$type = ( current_filter() == 'script_loader_src' ) ? 'js' : 'css';
			$source = ( current_filter() == 'script_loader_src' ) ? wp_scripts() : wp_styles();

			return ( $this->get_visibility_asset( $type, $handle ) ? $url : false);
		}
	}

	/**
	 * Get information regarding used assets
	 *
	 * @return bool
	 */
	public function collect_assets() {
		$denied = array(
			'js'  => array( 'whippet', 'admin-bar' ),
			'css' => array( 'whippet', 'admin-bar', 'dashicons' ),
		);

		/**
		 * Imitate full untouched list without dequeued assets
		 * Appends part of original table. Safe approach.
		 */
		$data_assets = array(
			'js'  => wp_scripts(),
			'css' => wp_styles(),
		);

		foreach ( $data_assets as $type => $data ) {
			foreach ( $data->done as $el ) {
				if ( ! in_array( $el, $denied[ $type ] ) ) {
					if ( isset( $data->registered[ $el ]->src ) ) {
						$url       = $this->prepare_correct_url( $data->registered[ $el ]->src );
						$url_short = str_replace( get_home_url(), '', $url );

						if ( false !== strpos( $url, get_theme_root_uri() ) ) {
							$resource_name = 'theme';
						} elseif ( false !== strpos( $url, plugins_url() ) ) {
							$resource_name = 'plugins';
						} else {
							$resource_name = 'misc';
						}

						$this->collection[ $resource_name ][ $type ][ $el ] = array(
							'url_full'  => $url,
							'url_short' => $url_short,
							'state'     => $this->get_visibility( $type, $el ),
							'size'      => $this->get_asset_size( $url ),
							'deps'      => ( isset( $data->registered[ $el ]->deps ) ? $data->registered[ $el ]->deps : array() ),
						);
					}
				}
			}
		}

		return false;
	}

	/**
	 * Initialize interface translation
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'whippet', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Adds notification after plugin activation how to use whippet
	 */
	public function load_plugin() {
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			if ( get_option( 'whippet_Issue_1' ) ) {
				delete_option( 'whippet_Issue_1' );
				deactivate_plugins( plugin_basename( __FILE__ ) );

			} elseif ( get_option( 'whippet_Issue_2' ) ) {
				delete_option( 'whippet_Issue_2' );
				deactivate_plugins( plugin_basename( __FILE__ ) );

			}
		}

		if ( is_admin() && 'whippet' == get_option( 'Activated_Plugin' ) ) {
			delete_option( 'Activated_Plugin' );
		}
	}


	/**
	 * Loads functionality that allows to enable/disable js/css without site reload
	 */
	public function append_asset() {
		if ( current_user_can( 'manage_options' ) ) {
			wp_enqueue_style( 'whippet', plugins_url( '../dist/css/style-whippet.css', __FILE__ ), array(), '1.0.1', false );
			wp_enqueue_script( 'whippet', plugins_url( '../dist/js/app.js', __FILE__ ), array(), '1.0.1', true );
		}
	}

	/**
	 * Get asset type based on name/ID
	 *
	 * @param  int|string $input Handler type.
	 * @return int|string        Reversed handler type.
	 */
	private function get_handler_type( $input ) {
		$data = array(
			'css' => 0,
			'js'  => 1,
		);

		if ( is_numeric( $input ) ) {
			$data = array_flip( $data );
		}

		return $data[ $input ];
	}

	/**
	 * Execute action once checkbox is changed
	 */
	public function update_configuration() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) ||
		 ! isset( $_POST['whippetUpdate'] ) ||
		 ! wp_verify_nonce( filter_input( INPUT_POST, 'whippetUpdate' ), 'whippet' ) ||
		 ! isset( $_POST['allAssets'] ) ||
		 empty( $_POST['allAssets'] ) ||
		 empty( $_POST['currentURL'] ) ) {
			return false;
		}

		$all_assets = json_decode( html_entity_decode( filter_input( INPUT_POST, 'allAssets', FILTER_SANITIZE_SPECIAL_CHARS ) ) );

		if ( empty( $all_assets ) ) {
			return false;
		}

		/**
		 * Clearing old configuration
		 * Removing all selected plugins (list of visible passed in array).
		 * Forget about phpcs warning. It's safe & prepared SQL
		 *
		 * 1. Clear disable everywhere
		 * 2. Clear enable content types & enable here
		 */
		$sql          = sprintf( 'DELETE FROM %s WHERE handler_name IN (%s) AND (url = "" OR url = "%s")', $wpdb->prefix . 'whippet_disabled', implode( ', ', array_fill( 0, count( $all_assets ), '%s' ) ), filter_input( INPUT_POST, 'currentURL' ) );
		$prepared_sql = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $all_assets ) );
		$wpdb->query( $prepared_sql );

		$sql          = sprintf( 'DELETE FROM %s WHERE handler_name IN (%s) AND (url = "" OR url = "%s")', $wpdb->prefix . 'whippet_enabled', implode( ', ', array_fill( 0, count( $all_assets ), '%s' ) ), filter_input( INPUT_POST, 'currentURL' ) );
		$prepared_sql = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), $all_assets ) );
		$wpdb->query( $prepared_sql );

		/**
		 * Inserting new configuration
		 */
		if ( isset( $_POST['disabled'] ) && ! empty( $_POST['disabled'] ) ) {
			foreach ( $_POST['disabled'] as $type => $assets ) {
				if ( ! empty( $assets ) ) {
					foreach ( $assets as $handle => $where ) {
						if ( ! empty( $where ) ) {
							foreach ( $where as $place => $nvm ) {
								$wpdb->insert(
									$wpdb->prefix . 'whippet_disabled',
									array(
										'handler_type' => $this->get_handler_type( $type ),
										'handler_name' => $handle,
										'url'          => ( 'here' == $place ? filter_input( INPUT_POST, 'currentURL' ) : '' ),
									),
									array( '%d', '%s', '%s' )
								);
							}
						}
					}
				}
			}
		}

		if ( isset( $_POST['enabled'] ) && ! empty( $_POST['enabled'] ) ) {
			foreach ( $_POST['enabled'] as $type => $assets ) {
				if ( ! empty( $assets ) ) {
					foreach ( $assets as $handle => $content_types ) {
						if ( ! empty( $content_types ) ) {
							foreach ( $content_types as $content_type => $nvm ) {
								$wpdb->insert(
									$wpdb->prefix . 'whippet_enabled',
									array(
										'handler_type' => $this->get_handler_type( $type ),
										'handler_name' => $handle,
										'content_type' => $content_type,
										'url'          => ( 'here' == $content_type ? filter_input( INPUT_POST, 'currentURL' ) : '' ),
									),
									array( '%d', '%s', '%s', '%s' )
								);
							}
						}
					}
				}
			}
		}

		/**
		 * Updating state of configuration after changes
		 */
		$this->load_configuration();

		if ( ! defined( 'WHIPPET_CACHE_CONTROL' ) ) {
			if ( function_exists( 'w3tc_pgcache_flush' ) ) {
				w3tc_pgcache_flush();
			} elseif ( function_exists( 'wp_cache_clear_cache' ) ) {
				wp_cache_clear_cache();
			} elseif ( function_exists( 'rocket_clean_files' ) ) {
				rocket_clean_files( esc_url( $_SERVER['HTTP_REFERER'] ) );
			}
		}
	}

	/**
	 * Generates Whippet item with dynamically generated subtrees in administration menu
	 *
	 * @param mixed $wp_admin_bar   Admin bar object.
	 */
	public function add_node_to_admin_bar( $wp_admin_bar ) {
		/**
		 * Checks whether Whippet should appear on frontend/backend or not
		 */
		if (
			! current_user_can( 'manage_options' ) ||
			( defined( 'WHIPPET_DISABLE_ON_FRONTEND' ) && ! is_admin() ) ||
			( ! defined( 'WHIPPET_ENABLE_ON_BACKEND' ) && is_admin() )
		) {
			return;
		}

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'whippet',
				'title' => esc_html__( 'Script Manager', 'whippet' ),
				'meta'  => array( 'class' => 'her-object' ),
			)
		);
	}

	/**
	 * Checks whether item is enabled/disabled
	 *
	 * @param  string $type   Handler type (CSS/JS).
	 * @param  string $plugin Handler name.
	 * @return bool          State
	 */
	private function get_visibility( $type = '', $plugin = '' ) {
		$state = true;

		if ( isset( $this->whippet_data['disabled'][ $type ][ $plugin ] ) ) {
			$state = false;

			if ( isset( $this->whippet_data['enabled'][ $type ][ $plugin ][ $this->content_type ] ) ||
				isset( $this->whippet_data['enabled'][ $type ][ $plugin ]['here'] ) ) {
				$state = true;
			}
		}

		return $state;
	}

	/**
	 * Exception for address starting from "//example.com" instead of
	 * "http://example.com". WooCommerce likes such a format
	 *
	 * @param  string $url Incorrect URL.
	 * @return string      Correct URL.
	 */
	private function prepare_correct_url( $url ) {
		if ( isset( $url[0] ) && isset( $url[1] ) && '/' == $url[0] && '/' == $url[1] ) {
			$out = ( is_ssl() ? 'https:' : 'http:' ) . $url;
		} else {
			$out = $url;
		}

		return $out;
	}

	/**
	 * Checks how heavy is file
	 *
	 * @param  string $src    URL.
	 * @return int          Size in KB.
	 */
	private function get_asset_size( $src ) {
		$weight = 0;

		$home = get_theme_root() . '/../..';
		$src  = explode( '?', $src );

		$src_relative = $home . str_replace( get_home_url(), '', $this->prepare_correct_url( $src[0] ) );

		if ( file_exists( $src_relative ) ) {
			$weight = round( filesize( $src_relative ) / 1024, 1 );
		}

		return $weight;
	}

	/**
	 * Detect current content type
	 */
	public function detect_content_type() {
		if ( is_singular() ) {
			$this->content_type = get_post_type();
		}
	}

	/**
	 * Making sure Whippet uses latest version of DB schema.
	 */
	private function check_db_integrity() {
		global $wpdb;
		global $whippet_db_version;

		if ( floatval( get_option( 'whippet_db_version' ) ) < $whippet_db_version ) {
			$table_name = $wpdb->prefix . 'whippet_disabled';
			$wpdb->query( "ALTER TABLE $table_name ADD url varchar(255) DEFAULT '' NOT NULL;" );
		}

		update_option( 'whippet_db_version', $whippet_db_version );
	}

	/**
	 * Reading saved configuration
	 */
	public function load_configuration() {
		$out = array();
		global $wpdb;

		/**
		 * Load_configuration function is executing before hooks so it's first
		 * function which uses database tables... potentially old tables.
		 * Make sure that I use latest version of db schema.
		 */
		$this->check_db_integrity();

		$disabled_global = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'whippet_disabled WHERE url = ""', ARRAY_A );
		$disabled_here   = $wpdb->get_results(
			sprintf(
				'SELECT * FROM ' . $wpdb->prefix . 'whippet_disabled WHERE url = "%s"',
				esc_url( $this->get_current_url() )
			), ARRAY_A
		);

		$enabled_posts = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'whippet_enabled WHERE content_type != "here"', ARRAY_A );
		$enabled_here  = $wpdb->get_results(
			sprintf(
				'SELECT * FROM %s WHERE content_type = \'%s\' AND url=\'%s\'',
				$wpdb->prefix . 'whippet_enabled',
				'here',
				esc_url( $this->get_current_url() )
			), ARRAY_A
		);
		$enabled       = array_merge( $enabled_here, $enabled_posts );

		if ( ! empty( $disabled_global ) ) {
			foreach ( $disabled_global as $row ) {
				$type = $this->get_handler_type( $row['handler_type'] );
				$out['disabled'][ $type ][ $row['handler_name'] ]['everywhere'] = true;
			}
		}

		if ( ! empty( $disabled_here ) ) {
			foreach ( $disabled_here as $row ) {
				$type = $this->get_handler_type( $row['handler_type'] );
				$out['disabled'][ $type ][ $row['handler_name'] ]['here'] = true;
			}
		}

		if ( ! empty( $enabled ) ) {
			foreach ( $enabled as $row ) {
				$type = $this->get_handler_type( $row['handler_type'] );
				$out['enabled'][ $type ][ $row['handler_name'] ][ $row['content_type'] ] = true;
			}
		}

		$this->whippet_data = $out;
	}

	/**
	 * Print render panel
	 */
	public function render_panel() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$out = isset( $_POST['whippetUpdate'] ) ? '<script>document.addEventListener("DOMContentLoaded", function(event) { document.getElementById("wp-admin-bar-whippet").click(); });</script>' : '';

		$out .= '<form id="whippet" class="whippet-panel" method="POST" style="display: none;">
		<h1>' . __( 'Welcome to whippet', 'whippet' ) . '</h1>
		<table class="whippet-info">
		<tr>
			<td>
			' . __( 'The Script Manager allows you to enable/disable CSS and JS files by a per page/post basis, as well as custom post types. Enabling some options may effect the appearance of your live site, so we recommend testing this locally or on a staging site first.', 'whippet' ) . '
            <br /><br />
			' . __( 'If you run into trouble, you can always enable all options to reset the settings. Make sure to check out the <a href="https://whippetwp.com/">Whippet Docs</a> for more information', 'whippet' ) . '
			</td>

		</tr>
		</table>';

		$all_assets    = array();
		$content_types = $this->get_public_post_types();
		krsort( $this->collection );

		foreach ( $this->collection as $resource_type => $types ) {
			$out .= '<h2>' . __( $resource_type, 'whippet' ) . '</h2>';

			$out .= '<table class="whippet-table">
				<thead>
					<th>' . __( 'Type', 'whippet' ) . '</th>
					<th>' . __( 'Size', 'whippet' ) . '</th>
					<th>' . __( 'Handle / URL', 'whippet' ) . '</th>
					<th>' . __( 'Disable', 'whippet' ) . '</th>
					<th>' . __( 'Enable', 'whippet' ) . '</th>
				</thead>
				<tbody>';

			foreach ( $types as $type_name => $rows ) {
				foreach ( $rows as $handle => $row ) {

					/**
					 * Find dependency
					 */
					$deps = array();
					foreach ( $rows as $dep_key => $dep_val ) {
						if ( in_array( $handle, $dep_val['deps'] ) && $dep_val['state'] ) {
							$deps[] = '<a href="#' . $type_name . '-' . $dep_key . '">' . $dep_key . '</a>';
						}
					}

					$id = '[' . $type_name . '][' . $handle . ']';

					$comment = ( ! empty( $deps ) ? '<span class="whippet-comment">' . __( 'In use by', 'whippet' ) . ' ' . implode( ', ', $deps ) . '</span>' : '' );

					// Disable everywhere.
					$id_ever           = 'disabled' . $id . '[everywhere]';
					$is_checked_ever   = ( isset( $this->whippet_data['disabled'][ $type_name ][ $handle ]['everywhere'] ) ? 'checked="checked"' : '' );
					$option_everywhere = '<div><input type="checkbox" name="' . $id_ever . '" id="' . $id_ever . '" ' . $is_checked_ever . '><label for="' . $id_ever . '">' . __( 'Everywhere', 'whippet' ) . '</label></div>';

					// Disable here.
					$id_curr             = 'disabled' . $id . '[here]';
					$is_checked_here     = ( isset( $this->whippet_data['disabled'][ $type_name ][ $handle ]['here'] ) ? 'checked="checked"' : '' );
					$is_disabled         = ( ! empty( $is_checked_ever ) ? 'disabled="disabled"' : '' );
					$option_disable_here = '<div class="disable-here" data-id="' . $id_ever . '"><input type="checkbox" name="' . $id_curr . '" id="' . $id_curr . '" ' . $is_checked_here . ' ' . $is_disabled . '><label for="' . $id_curr . '">' . __( 'Current URL', 'whippet' ) . '</label></div>';

					// Enable here.
					$id_curr        = 'enabled' . $id . '[here]';
					$is_checked     = ( isset( $this->whippet_data['enabled'][ $type_name ][ $handle ]['here'] ) ? 'checked="checked"' : '' );
					$is_disabled    = ( empty( $is_checked_ever ) ? 'disabled="disabled"' : '' );
					$options_enable = '<div><input type="checkbox" name="' . $id_curr . '" id="' . $id_curr . '" ' . $is_checked . ' ' . $is_disabled . '><label for="' . $id_curr . '">' . __( 'Current URL', 'whippet' ) . '</label></div>';

					// Enable custom type.
					foreach ( $content_types as $content_type_code => $content_type ) {
						$id_type         = 'enabled' . $id . '[' . $content_type_code . ']';
						$is_checked      = ( isset( $this->whippet_data['enabled'][ $type_name ][ $handle ][ $content_type_code ] ) ? 'checked="checked"' : '' );
						$is_disabled     = ( empty( $is_checked_ever ) ? 'disabled="disabled"' : '' );
						$options_enable .= '<div><input type="checkbox" name="' . $id_type . '" id="' . $id_type . '" ' . $is_checked . ' ' . $is_disabled . '><label for="' . $id_type . '">' . $content_type . '</label></div>';
					}

					$out     .= '<tr>';
						$out .= '<td><div class="state-' . (int) $row['state'] . '">' . strtoupper( $type_name ) . '</div></td>';
						$out .= '<td>' . ( empty( $row['size'] ) ? '?' : $row['size'] ) . ' KB</td>';
						$out .= '<td class="overflow"><h3><a name="' . $type_name . '-' . $handle . '">' . $handle . '</a></h3><a class="whippet-link" href="' . $row['url_full'] . '" target="_blank">' . $row['url_short'] . '</a></td>';
						$out .= '<td class="option-everwhere">' . $option_everywhere . $option_disable_here . $comment . '</td>';
						$out .= '<td class="options" data-id="' . $id_ever . '">' . $options_enable . '</td>';
					$out     .= '</tr>';

					$all_assets[] = $handle;
				}
			}
			$out .= '</tbody>
			</table>';
		}

		$out .= '<input type="submit" id="submit-whippet" value="' . __( 'Save changes', 'whippet' ) . '">';
		$out .= wp_nonce_field( 'whippet', 'whippetUpdate', true, false );
		$out .= '<input type="hidden" name="currentURL" value="' . esc_url( $this->get_current_url() ) . '">
			<input type="hidden" name="allAssets" value="' . filter_var( json_encode( $all_assets ), FILTER_SANITIZE_SPECIAL_CHARS ) . '">
		</form>';

		print $out;
	}

	/**
	 * Get current URL
	 *
	 * @return string
	 */
	private function get_current_url() {
		$url = explode( '?', $_SERVER['REQUEST_URI'], 2 );
		if ( strlen( $url[0] ) > 1 ) {
			$out = rtrim( $url[0], '/' );
		} else {
			$out = $url[0];
		}

		return $out;
	}

	/**
	 * Generated content types
	 *
	 * @return mixed
	 */
	private function get_public_post_types() {
		$tmp = get_post_types(
			array(
				'public' => true,
			), 'objects', 'and'
		);

		$out = array();
		foreach ( $tmp as $key => $value ) {
			$out[ $key ] = $value->label;
		}

		return $out;
	}
}

new Whippet();
