<?php

class Media_Manager_Plus_Sources {

	function __construct() {
		$this->include_sources();
		add_action( 'admin_init', array( $this, 'image_sources_header' ) );
		add_action( 'wp_ajax_uber_disconnect', array( $this, 'disconnect_source' ) );
		add_action( 'wp_ajax_uber_check', array( $this, 'connect_check' ) );
		add_action( 'wp_ajax_uber_load_images', array( $this, 'load_images' ) );
		add_action( 'wp_ajax_uber_param_choices', array( $this, 'param_choices' ) );
	} // END  __construct()

	/**
	 * Load all the sources
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function include_sources() {
		$plugin_dir = Media_Manager_Plus::get_value('plugin_dir');
		require_once $plugin_dir . 'includes/oauth/provider.php';
		$source_dir = glob( $plugin_dir . 'includes/sources/*.php' );
		if ( $source_dir ) {
			foreach ( $source_dir as $dir ) {
				include_once( $dir );
			}
		}
	} // END get_extensions()

	/**
	 * Control the connecting of image sources
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function image_sources_header() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'mmp' && isset( $_GET['type'] ) ) {
			$options = mmp_default_val( 'ubermediasettings_sources_available', array() );
			$source  = $_GET['type'];

			$request_code = $_GET['oauth_verifier'];

			if ( isset( $_SESSION[$source . '_oauth_token'] ) && isset( $_SESSION[$source . '_oauth_token_secret'] ) ) {

				$auth_token        = $_SESSION[$source . '_oauth_token'];
				$auth_token_secret = $_SESSION[$source . '_oauth_token_secret'];

				$callback = Media_Manager_Plus::get_value('callback_url');
				$var      = 'media_manager_plus_source_' . $source;
				$obj      = new $var( $auth_token, $auth_token_secret );

				$token = $obj->getAccessToken( $request_code, $callback );

				if ( isset( $token['oauth_token'] ) && isset( $token['oauth_token_secret'] ) ) {
					$options[$source . '-settings'] = array( 'access-token' => $token );

					$save_options = media_manager_plus()->settings->settings;

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
	} // END image_sources_header()

	/**
	 * Disconnect image sources
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int
	 */
	public function disconnect_source() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uber_media' ) ) {
			return 0;
		}
		if ( ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$source              = $_POST['source'];
		$options             = mmp_default_val( 'ubermediasettings_sources_available', array() );
		if ( isset( $options[$source . '-settings'] ) ) {
			unset( $options[$source . '-settings'] );
			$save_options = media_manager_plus()->settings->settings;

			$save_options['ubermediasettings_sources_available'] = $options;
			update_option( 'ubermediasettings_settings', $save_options );
			$response['message'] = 'success';
		}
		echo json_encode( $response );
		die;
	} // END disconnect_source()

	/**
	 * Check which sources are connected
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int
	 */
	public function connect_check() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uber_media' ) ) {
			return 0;
		}
		if ( ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$source              = $_POST['source'];
		$options             = mmp_default_val( 'ubermediasettings_sources_available', array() );
		if ( isset( $options[$source . '-settings'] ) ) {
			$response['message'] = 'success';
		}
		echo json_encode( $response );
		die;
	} // END connect_check()

	/**
	 * Load the images for a source
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int
	 */
	public function load_images() {
		if ( ! isset( $_POST['param'] ) || ! isset( $_POST['method'] ) || ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$response['images']  = array();
		$images              = array();

		$image_source = $_POST['source'];
		$options      = mmp_default_val( 'ubermediasettings_sources_available', array() );
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
			$safemode           = mmp_default_val( 'ubermediasettings_general_safe-mode', 1 );
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
	} // END load_images()

	/**
	 * Return parameter choices for the image source
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int
	 */
	public function param_choices() {
		if ( ! isset( $_POST['method'] ) || ! isset( $_POST['source'] ) ) {
			return 0;
		}
		$response['error']   = false;
		$response['message'] = '';
		$response['choices'] = array();
		$choices             = array();

		$image_source = $_POST['source'];
		$options      = mmp_default_val( 'ubermediasettings_sources_available', array() );
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
	} // END param_choices()

	/**
	 * Return images sources
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $popup
	 *
	 * @return array
	 */
	public function get_sources( $popup = false ) {
		$options        = mmp_default_val( 'ubermediasettings_sources_available', array() );
		$show_connected = mmp_default_val( 'ubermediasettings_general_show-connected', 0 );
		$callback       = Media_Manager_Plus::get_value('callback_url');
		$load_sources   = apply_filters( 'uber_media_sources', array() );
		$sources        = array();
		if ( $load_sources ) {
			foreach ( $load_sources as $source => $source_details ) {
				$source_data['url']      = '#';
				$source_data['name']     = isset( $source_details['name'] ) ? $source_details['name'] : ucfirst( $source );
				$var                     = 'media_manager_plus_source_' . $source;
				$obj                     = new $var();
				$source_data['settings'] = $obj->show_details();

				if ( isset( $source_details['core'] ) ) {
					$source_data['imgsrc'] = Media_Manager_Plus::get_value('plugin_url') . 'assets/img/' . $source . '.png';
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
	} // END get_sources()

} // END Media_Manager_Plus_Sources