<?php
/*
Plugin Name: Media Manager Plus
Plugin URI: http://dev7studios.com/media-manager-plus
Description: Upgrade the WordPress Media Manager and add support for Flickr, Instagram, 500px etc.
Version: 1.5
Author: Dev7studios
Author URI: http://dev7studios.com
Text Domain: media-manager-plus
Domain Path: /lang
*/

if ( ! session_id() ) {
	session_start();
}

$uber_media = new uber_media();
class uber_media {

	private $plugin_folder;
	private $plugin_path;
	private $plugin_version;
	private $extensions_url;
	private $callback;

	function __construct() {

		$this->plugin_version = '1.5';
		$this->plugin_folder  = basename( plugin_dir_path( __FILE__ ) );
		$this->plugin_path    = plugin_dir_path( __FILE__ );
		$this->extensions_url = 'http://cdn.dev7studios.com/media-manager-plus/extensions.json?v=1.1';
		$this->callback       = get_admin_url() . 'upload.php?page=uber-media';

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ) );

		add_action( 'admin_init', array( $this, 'upgrade_check' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );

		add_action( 'admin_init', array( $this, 'welcome' ) );
		add_action( 'admin_init', array( $this, 'image_sources_header' ) );

		add_action( 'print_media_templates', array( $this, 'print_media_templates' ), 99 );
		add_filter( 'media_view_strings', array( $this, 'custom_media_string' ), 10, 2 );

		add_action( 'wp_ajax_uber_disconnect', array( $this, 'disconnect_source' ) );
		add_action( 'wp_ajax_uber_check', array( $this, 'connect_check' ) );
		add_action( 'wp_ajax_uber_load_images', array( $this, 'load_images' ) );
		add_action( 'wp_ajax_uber_param_choices', array( $this, 'param_choices' ) );

		add_action( 'wp_ajax_uber_pre_insert', array( $this, 'pre_insert' ) );

		$this->include_sources();

		require_once( $this->plugin_path . 'includes/wp-settings-framework.php' );
		$this->wpsf = new ubermediaWordPressSettingsFramework( $this->plugin_path . 'includes/uber-media-settings.php', '' );
		add_filter( $this->wpsf->get_option_group() . '_settings_validate', array( $this, 'validate_settings' ) );
		$this->settings = wpsf_get_settings( $this->plugin_path . 'includes/uber-media-settings.php' );

	}

	function activate( $network_wide ) {
		set_transient( '_mmp_activation_redirect', true, 30 );
	}

	function upgrade_check() {
		if ( ! get_option( 'mmp_version' ) ) {
			add_option( 'mmp_version', $this->plugin_version );

			return;
		}
		$current_version = get_option( 'mmp_version' );
		if ( version_compare( $current_version, $this->plugin_version, '!=' ) ) {
			// Large upgrade to v1.2
			if ( version_compare( $current_version, '1.2', '<' ) ) {
				set_transient( '_mmp_activation_redirect', true, 30 );
			}
			update_option( 'mmp_version', $this->plugin_version );
		}
	}

	/**
	 * Register and enqueue admin-specific assets.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hook Current page hook
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {

		$pages       = array( 'media_page_uber-media', 'post.php', 'post-new.php' );
		$admin_pages = apply_filters( 'uber_media_enqueue_pages', $pages );
		$dev         = apply_filters( 'uber_media_debug_mode', SCRIPT_DEBUG ) ? '' : '.min';

		if ( in_array( $hook, $admin_pages ) ) {

			wp_enqueue_media();
			wp_register_script( 'uber-media-js', plugins_url( "assets/js/uber-media{$dev}.js" , __FILE__ ), array( 'media-views' ), $this->plugin_version );
			wp_enqueue_script( 'uber-media-js' );
			wp_localize_script( 'uber-media-js', 'uber_media', array( 'nonce' => wp_create_nonce( 'uber_media' ) ));

			wp_register_style( 'uber-media-css', plugins_url( "assets/css/uber-media{$dev}.css" , __FILE__ ), array(), $this->plugin_version );
			wp_enqueue_style( 'uber-media-css' );

		} // END if

    } // END admin_enqueue_scripts()

	function admin_menu() {
		add_media_page(
			__( 'Media Manager Plus', 'media-manager-plus' ),
			__( 'Media Manager Plus', 'media-manager-plus' ),
			'read',
			'uber-media',
			array( $this, 'settings_page' )
		);
		add_dashboard_page(
			__( 'Welcome to Media Manager Plus', 'media-manager-plus' ),
			__( 'Media Manager Plus', 'media-manager-plus' ),
			'read',
			'mmp-welcome',
			array( $this, 'welcome_screen' )
		);
		add_dashboard_page(
			__( 'Welcome to Media Manager Plus', 'media-manager-plus' ),
			__( 'Media Manager Plus', 'media-manager-plus' ),
			'read',
			'mmp-support',
			array( $this, 'support_screen' )
		);
	}

	public function admin_head() {
		remove_submenu_page( 'index.php', 'mmp-welcome' );
		remove_submenu_page( 'index.php', 'mmp-support' );
	}

	public function welcome() {
		if ( ! get_transient( '_mmp_activation_redirect' ) ) {
			return;
		}
		delete_transient( '_mmp_activation_redirect' );
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}
		wp_safe_redirect( admin_url( 'index.php?page=mmp-welcome' ) );
		exit;
	}

	public function welcome_screen() {
		?>
		<div class="wrap about-wrap mmp-welcome">
			<h1><?php printf( __( 'Welcome to Media Manager Plus', 'media-manager-plus' ), $this->plugin_version ); ?></h1>

			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Media Manager Plus %s upgrades the WordPress media manager with third party image sources.', 'media-manager-plus' ), $this->plugin_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp-welcome' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New", 'media-manager-plus' ); ?>
				</a>
				<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp-support' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Support', 'media-manager-plus' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Introducing Extensions', 'media-manager-plus' ); ?></h3>
				<?php $this->get_extensions(); ?>
			</div>
			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'uber-media' ), 'upload.php' ) ) ); ?>"><?php _e( 'Go to Media Manager Plus Settings', 'media-manager-plus' ); ?></a>
			</div>
		</div>
	<?php
	}

	public function support_screen() {
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Media Manager Plus', 'media-manager-plus' ), $this->plugin_version ); ?></h1>

			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Media Manager Plus %s upgrades the WordPress media manager with third party image sources.', 'media-manager-plus' ), $this->plugin_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp-welcome' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New", 'media-manager-plus' ); ?>
				</a>
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp-support' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Support', 'media-manager-plus' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Get Some Help', 'media-manager-plus' ); ?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Website', 'media-manager-plus' ); ?></h4>

						<p>
							<a target="_blank" href="http://dev7studios.com/media-manager-plus">Media Manager Plus</a>
						</p>
						<h4><?php _e( 'Created by', 'media-manager-plus' ); ?></h4>

						<p>
							<a target="_blank" href="http://dev7studios.com">Dev7studios</a>
						</p>
						<h4><?php _e( 'Support', 'media-manager-plus' ); ?></h4>

						<p>
							<a target="_blank" href="http://support.dev7studios.com/discussions/media-manager-plus-wordpress-plugin"><?php _e( 'Support Forums', 'media-manager-plus' ); ?></a>
						</p>
						<h4><?php _e( 'Changelog', 'media-manager-plus' ); ?></h4>

						<p>
							<a target="_blank" href="http://wordpress.org/extend/plugins/uber-media/changelog"><?php _e( 'Changelog', 'media-manager-plus' ); ?></a>
						</p>
					</div>
					<div>
						<h4><?php _e( 'Watch The Video', 'media-manager-plus' ); ?></h4>

						<div class='video'>
							<object width='532' height='325'>
								<param name='movie' value='http://www.youtube.com/v/dR0sPNSICfk?fs=1'></param>
								<param name='allowFullScreen' value='true'></param>
								<param name='allowscriptaccess' value='never'></param>
								<embed src='http://www.youtube.com/v/dR0sPNSICfk?fs=1' type='application/x-shockwave-flash' allowscriptaccess='never' allowfullscreen='true' width='532' height='325'></embed>
							</object>
						</div>
					</div>
				</div>
			</div>
			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'uber-media' ), 'upload.php' ) ) ); ?>"><?php _e( 'Go to Media Manager Plus Settings', 'media-manager-plus' ); ?></a>
			</div>
		</div>
	<?php
	}


	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}
		global $wpsf_ubermedia_settings;
		$wpsf_ubermedia_settings = apply_filters( 'uber_media_settings', $wpsf_ubermedia_settings );
		$active_tab              = isset( $_GET['tab'] ) ? $_GET['tab'] : 'sources';
		?>
		<div class="wrap">
			<div id="icon-upload" class="icon32"></div>
			<h2><?php _e( 'Media Manager Plus', 'media-manager-plus' ); ?>
				<span class="uber-version">v <?php echo $this->plugin_version; ?></span></h2>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $wpsf_ubermedia_settings as $tab ) { ?>
					<a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $tab['section_id']; ?>" class="nav-tab<?php echo( $active_tab == $tab['section_id'] ? ' nav-tab-active' : '' ); ?>"><?php echo $tab['section_title']; ?></a>
				<?php } ?>
			</h2>

			<form action="options.php" method="post">
				<?php settings_fields( $this->wpsf->get_option_group() ); ?>
				<?php $this->do_settings_sections( $this->wpsf->get_option_group() ); ?>
			</form>
		</div>
	<?php
	}

	function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'sources';
		if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[$page] ) ) {
			return;
		}
		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			echo '<div id="section-' . $section['id'] . '"class="ubermedia-section' . ( $active_tab == $section['id'] ? ' ubermedia-section-active' : '' ) . '">';

			if ( $section['id'] == 'sources' ) {
				$this->setting_image_sources();
			} else {
				if ( $section['id'] == 'extensions' ) {
					$this->setting_extensions();
				} else {
					call_user_func( $section['callback'], $section );
					if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[$page] ) || ! isset( $wp_settings_fields[$page][$section['id']] ) ) {
						continue;
					}
					echo '<table class="form-table">';
					echo '<input type="hidden" name="ubermediasettings_settings[sources]" value="sources">';
					do_settings_fields( $page, $section['id'] );
					echo '</table>';
					if ( $section['id'] != 'support' ) {
						submit_button();
					}
				}
			}
			echo '</div>';
		}
	}

	function validate_settings( $input ) {
		if ( isset( $input['sources'] ) ) {
			$sources                                      = $this->default_val( $this->settings, 'ubermediasettings_sources_available', array() );
			$input['ubermediasettings_sources_available'] = $sources;
			unset( $input['sources'] );
		}

		return $input;
	}

	function default_val( $options, $value, $default = '' ) {
		if ( ! isset( $options[$value] ) ) {
			return $default;
		} else {
			return $options[$value];
		}
	}

	function setting_image_sources() {
		$sources = $this->get_sources();
		$html    = '';
		if ( $sources ) {
			$html .= '<div id="uber-media-settings">';
			$html .= '<iframe id="logoutframe" src="https://instagram.com/accounts/logout/" width="0" height="0"></iframe>';
			$html .= '<ul>';
			foreach ( $sources as $source => $source_data ) {
				$class    = ( $source_data['url'] == '#' ) ? 'disconnect' : 'connect';
				$text     = ucfirst( $class );
				$disabled = '';
				$title    = ucfirst( $class ) . ' ' . $source_data['name'];
				$btnclass = ( $source_data['url'] == '#' ) ? '' : ' button-primary';
				if ( $source_data['url'] == '' ) {
					$text     = 'API Unavailable';
					$class    = 'disconnect';
					$disabled = 'disabled="disabled" ';
					$btnclass = '';
					$title    = $source_data['name'] . ' API Unavailable';
				}

				$width  = ( isset( $source_data['w'] ) ) ? 'data-w="' . $source_data['w'] . '" ' : '';
				$height = ( isset( $source_data['h'] ) ) ? 'data-h="' . $source_data['h'] . '" ' : '';
				$html .= '<li>';
				$html .= '<img src="' . $source_data['imgsrc'] . '" alt="' . $source_data['name'] . ' logo">';
				$html .= '<a ' . $disabled . 'data-source="' . $source . '" ' . $width . $height . 'class="button uber-connect ' . $class . $btnclass . '" title="' . $title . '" href="' . $source_data['url'] . '">' . $text . '</a></li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}
		if ( $html == '' ) {
			$html = __( 'No available sources', 'media-manager-plus' );
		}
		echo $html;
	}

	function setting_extensions() {
		$this->get_extensions();
	}

	function get_installed_extensions() {
		$extensions = array();
		$plugins    = wp_get_active_and_valid_plugins();
		foreach ( $plugins as $key => $plugin ) {
			$pos = strpos( $plugin, 'media-manager-plus' );
			if ( $pos !== false ) {
				$extensions[] = basename( $plugin, ".php" );
			}
		}

		return $extensions;
	}

	function get_extensions() {
		$result     = wp_remote_get( $this->extensions_url );
		$extensions = array();
		if ( 200 == $result['response']['code'] ) {
			$json       = json_decode( $result['body'] );
			$extensions = $json->extensions;
		}
		$html = '';
		if ( $extensions ) {
			$html .= '<div id="uber-media-extensions">';
			$html .= '<ul>';
			$count = 0;
			foreach ( $extensions as $extension_data ) {
				$plugin_name     = $extension_data->pluginFile;
				$validate_plugin = validate_plugin( $plugin_name );
				if ( ! is_wp_error( $validate_plugin ) && $validate_plugin == 0 ) {
					continue;
				}
				$count ++;
				$html .= '<li>';
				$html .= '<h3><a href="' . $extension_data->link . '" target="_blank">' . $extension_data->name . '</a></h3>';
				$html .= '<a href="' . $extension_data->link . '" target="_blank"><img src="' . $extension_data->image . '" alt="' . $extension_data->name . ' logo"></a>';
				$html .= '<p>' . $extension_data->description . '</p>';
				if ( version_compare( $this->plugin_version, $extension_data->requires, '<' ) ) {
					$html .= '<p><strong>Requires Version ' . $extension_data->requires . '</strong></p>';
				}
				$html .= '<a target="_blank" class="button button-primary" title="Buy the ' . $extension_data->name . ' extension for $' . $extension_data->price . '" href="' . $extension_data->link . '">$' . $extension_data->price . ' Buy</a>';
				$html .= '</li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}
		if ( $html == '' || $count == 0 ) {
			$html = __( 'No new extensions available, you must have installed them all. Nice.', 'media-manager-plus' );
		}
		echo $html;
	}

	function include_sources() {
		require_once( dirname( __FILE__ ) . '/includes/oauth/provider.php' );
		$source_dir = glob( dirname( __FILE__ ) . '/includes/sources/*.php' );
		if ( $source_dir ) {
			foreach ( $source_dir as $dir ) {
				include_once( $dir );
			}
		}
	}

	function get_sources( $popup = false ) {
		$options        = $this->default_val( $this->settings, 'ubermediasettings_sources_available', array() );
		$show_connected = $this->default_val( $this->settings, 'ubermediasettings_general_show-connected', 0 );
		$callback       = $this->callback;
		$load_sources   = apply_filters( 'uber_media_sources', array() );
		$sources        = array();
		if ( $load_sources ) {
			foreach ( $load_sources as $source => $source_details ) {
				$source_data['url']      = '#';
				$source_data['name']     = ucfirst( $source );
				$var                     = 'media_manager_plus_source_' . $source;
				$obj                     = new $var();
				$source_data['settings'] = $obj->show_details();

				if ( isset( $source_details['core'] ) ) {
					$source_data['imgsrc'] = plugins_url( 'assets/img/' . $source . '.png', __FILE__ );
				} else {
					$source_data['imgsrc'] = $source_details['imgsrc'];
				}

				if ( ! array_key_exists( $source . '-settings', $options ) && ( ! $popup || ( $popup && $show_connected == 0 ) ) ) {
					$source_data['url'] = $obj->get_authorise_url( $callback, $source );
					$source_data['w']   = $obj->get_popup_width();
					$source_data['h']   = $obj->get_popup_height();
				}
				if ( ! $popup ||
					 ( $popup && $show_connected == 0 ) ||
					 ( $popup && $show_connected == 1 && array_key_exists( $source . '-settings', $options ) )
				) {
					$sources[$source] = $source_data;
				}

				if ( $source_data['url'] == '' && $popup ) {
					unset( $sources[$source] );
				}
			}
		}
		ksort( $sources );

		return $sources;
	}

	function disconnect_source() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uber_media' ) ) {
			return 0;
		}
		if ( ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$source              = $_POST['source'];
		$options             = $this->default_val( $this->settings, 'ubermediasettings_sources_available', array() );
		if ( isset( $options[$source . '-settings'] ) ) {
			unset( $options[$source . '-settings'] );
			$save_options                                        = $this->settings;
			$save_options['ubermediasettings_sources_available'] = $options;
			update_option( 'ubermediasettings_settings', $save_options );
			$response['message'] = 'success';
		}
		echo json_encode( $response );
		die;
	}

	function connect_check() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uber_media' ) ) {
			return 0;
		}
		if ( ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$source              = $_POST['source'];
		$options             = $this->default_val( $this->settings, 'ubermediasettings_sources_available', array() );
		if ( isset( $options[$source . '-settings'] ) ) {
			$response['message'] = 'success';
		}
		echo json_encode( $response );
		die;
	}

	function image_sources_header() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'uber-media' && isset( $_GET['type'] ) ) {
			$options = $this->default_val( $this->settings, 'ubermediasettings_sources_available', array() );
			$source  = $_GET['type'];

			$request_code = $_GET['oauth_verifier'];

			if ( isset( $_SESSION[$source . '_oauth_token'] ) && isset( $_SESSION[$source . '_oauth_token_secret'] ) ) {

				$auth_token        = $_SESSION[$source . '_oauth_token'];
				$auth_token_secret = $_SESSION[$source . '_oauth_token_secret'];

				$callback = $this->callback;
				$var      = 'media_manager_plus_source_' . $source;
				$obj      = new $var( $auth_token, $auth_token_secret );

				$token = $obj->getAccessToken( $request_code, $callback );

				if ( isset( $token['oauth_token'] ) && isset( $token['oauth_token_secret'] ) ) {
					$options[$source . '-settings'] = array( 'access-token' => $token );

					$save_options                                        = $this->settings;
					$save_options['ubermediasettings_sources_available'] = $options;
					update_option( 'ubermediasettings_settings', $save_options );

					if ( isset( $_SESSION[$source . '_oauth_token'] ) ) {
						unset( $_SESSION[$source . '_oauth_token'] );
					}
					if ( isset( $_SESSION[$source . '_oauth_token_secret'] ) ) {
						unset( $_SESSION[$source . '_oauth_token_secret'] );
					}
				}
			}
			?>
			<script>
				window.close();
			</script>
		<?php
		}
	}

	function param_choices() {
		if ( ! isset( $_POST['method'] ) || ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$response['choices'] = array();
		$choices             = array();

		$image_source = $_POST['source'];
		$options      = $this->default_val( $this->settings, 'ubermediasettings_sources_available', array() );
		if ( isset( $options[$image_source . '-settings'] ) ) {
			$source_settings = $options[$image_source . '-settings'];
			$access_token    = $source_settings['access-token'];
			$var             = 'media_manager_plus_source_' . $image_source;
			$obj             = new $var( $access_token['oauth_token'], $access_token['oauth_token_secret'] );
			$method          = $_POST['method'];

			$return = $obj->get_param_choices( $method );

			if ( $return ) {
				$choices = $return;
			} else {
				$response['error']   = true;
				$response['message'] = 'Failed to get choices for ' . $method;
			}
		}
		$response['choices'] = $choices;
		echo json_encode( $response );
		die;
	}

	function load_images() {
		if ( ! isset( $_POST['param'] ) || ! isset( $_POST['method'] ) || ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$response['images']  = array();
		$images              = array();

		$image_source = $_POST['source'];
		$options      = $this->default_val( $this->settings, 'ubermediasettings_sources_available', array() );
		if ( isset( $options[$image_source . '-settings'] ) ) {
			$source_settings = $options[$image_source . '-settings'];
			$access_token    = $source_settings['access-token'];
			$var             = 'media_manager_plus_source_' . $image_source;
			$obj             = new $var( $access_token['oauth_token'], $access_token['oauth_token_secret'] );
			$method          = $_POST['method'];
			$count           = 50;
			$params          = array();
			if ( isset( $_POST['param'] ) && $_POST['param'] != '' ) {
				$params[] = $_POST['param'];
			}
			if ( $count != '' ) {
				$params['count'] = $count;
			}
			$safemode           = $this->default_val( $this->settings, 'ubermediasettings_general_safe-mode', 1 );
			$params['safemode'] = $safemode;
			if ( isset( $_POST['page'] ) && $_POST['page'] != '' ) {
				$params['page'] = $_POST['page'];
			}
			if ( isset( $_POST['altpage'] ) && $_POST['altpage'] != '' ) {
				$params['altpage'] = $_POST['altpage'];
			}
			$return = call_user_func_array( array( $obj, $method ), $params );
			if ( $return['images'] ) {
				foreach ( $return['images'] as $image ) {
					$images[] = $image;
				}
				if ( isset( $return['pagin'] ) ) {
					$response['pagin'] = 'end';
				}
				if ( isset( $return['altpage'] ) ) {
					$response['altpage'] = $return['altpage'];
				}
			} else {
				$response['error']   = true;
				$response['message'] = 'Failed to get ' . ucfirst( $image_source ) . ' images' . ( ( isset( $_POST['param'] ) && $_POST['param'] != '' ) ? ' for ' . $_POST['param'] : '' );
			}
		}
		$response['images'] = $images;

		echo json_encode( $response );
		die;
	}

	function custom_media_string( $strings, $post ) {
		$hier                       = $post && is_post_type_hierarchical( $post->post_type );
		$strings['ubermedia']       = $this->get_sources( true );
		$strings['ubermediaButton'] = $hier ? __( 'Insert into page', 'media-manager-plus' ) : __( 'Insert into post', 'media-manager-plus' );
		$strings['mmpImportButton'] = __( 'Import', 'media-manager-plus' );
		$strings['mmp_menu']        = apply_filters( 'mmp_default_menu', 'default' );
		$strings['mmp_menu_prefix'] = apply_filters( 'mmp_menu_prefix', __( 'Insert from ', 'media-manager-plus' ) );
		$strings['mmp_defaults']    = apply_filters( 'mmp_default_settings', array() );
		$strings['mmp_extensions']  = $this->get_installed_extensions();

		return $strings;
	}

	function pre_insert() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uber_media' ) ) {
			return 0;
		}
		if ( ! isset( $_POST['imgsrc'] ) ) {
			return 0;
		}

		$response['error']   = false;
		$response['message'] = 'success';
		$response['imgsrc']  = $_POST['imgsrc'];
		$response['fields']  = $_POST;

		echo json_encode( apply_filters( 'uber_media_pre_insert', $response ) );
		die;
	}

	function print_media_templates() {
		?>
		<script type="text/html" id="tmpl-uberimage">
			<img id="{{ data.id }}" class="<# if ( typeof(data.folder) !== 'undefined' ) { #>folder<# } else { #>image<# } #>" src="{{ data.thumbnail }}" alt="{{ data.caption }}" title="{{ data.caption }}" data-full="{{ data.full }}" data-link="{{ data.link }}" />
			<# if ( typeof(data.folder) !== 'undefined' ) { #>
				<p>{{ data.caption }}</p>
				<# } #>
					<a class="check" id="check-link-{{ data.id }}" href="#" title="Deselect">
						<div id="check-{{ data.id }}" class="media-modal-icon"></div>
					</a>
		</script>
		<script type="text/html" id="tmpl-uberimage-settings">
			<div class="attachment-info">
				<h3>{{{ data.selected_image.title }}}</h3>
				<span id="uberload" class="spinner" style="display: block"></span>
				<input id="full-uber" type="hidden" value="{{ data.selected_image.dataset.full }}" />
				<input id="uber-id" type="hidden" value="{{ data.selected_image.id }}" />

				<div class="thumbnail">
				</div>
			</div>
			<?php do_action('uber_media_settings_before'); ?>
			<?php if ( ! apply_filters( 'disable_captions', '' ) ) : ?>
				<label class="setting caption">
				<span><?php _e('Caption', 'media-manager-plus'); ?></span>
				<textarea id="caption-uber" data-setting="caption"></textarea>
			</label>
			<?php endif; ?>
			<label class="setting alt-text">
				<span><?php _e('Title', 'media-manager-plus'); ?></span>
				<input id="title-uber" type="text" data-setting="title" value="{{{ data.selected_image.title }}}" />
				<input name="original-title" type="hidden" value="{{{ data.selected_image.title }}}" />
			</label>
			<label class="setting alt-text">
				<span><?php _e('Alt Text', 'media-manager-plus'); ?></span>
				<input id="alt-uber" type="text" data-setting="alt" value="{{{ data.selected_image.title }}}" />
			</label>
			<div class="setting align">
				<span><?php _e('Align', 'media-manager-plus'); ?></span>
				<select class="alignment" data-setting="align" name="uber-align">
					<option value="left"> <?php esc_attr_e('Left'); ?> </option>
					<option value="center"> <?php esc_attr_e('Center'); ?> </option>
					<option value="right"> <?php esc_attr_e('Right'); ?> </option>
					<option selected="selected" value="none"> <?php esc_attr_e('None'); ?> </option>
				</select>
			</div>
			<div class="setting link-to">
				<span><?php _e('Link To', 'media-manager-plus'); ?></span>
				<select class="link-to" data-setting="link-to" name="uber-link">
					<option value="{{ data.selected_image.dataset.full }}"> <?php esc_attr_e('Image URL'); ?> </option>
					<option value="{{ data.selected_image.dataset.link }}"> <?php esc_attr_e('Page URL'); ?> </option>
					<option selected="selected" value="none"> <?php esc_attr_e('None'); ?> </option>
				</select>
			</div>
			<?php do_action('uber_media_settings_after'); ?>
		</script>
	<?php
	}

	/**
	 * Load the plugin's textdomain hooked to 'plugins_loaded'.
	 *
	 * @since	1.5.0
	 * @access	public
	 *
	 * @see		load_plugin_textdomain()
	 * @see		plugin_basename()
	 * @action	plugins_loaded
	 *
	 * @return	void
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'media-manager-plus',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/lang/'
		);

	} // END load_plugin_textdomain()

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.5.0
	 * @access   public
	 *
	 * @see      admin_url()
	 *
	 * @param    array $links Array of links
	 * @return   array Array of links
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . add_query_arg( array( 'page' => 'uber-media' ), admin_url( 'upload.php' ) ) . '">' . __( 'Settings' ) . '</a>'
			),
			$links
		);

	} // END add_action_links()

}

?>