<?php

require_once( 'oauth.php' );

/**
 * Generic Provider OAuth class
 */
if ( ! class_exists( 'media_manager_plus_source' ) ) {
	class media_manager_plus_source {
		/* Contains the last HTTP status code returned. */
		public $http_code;
		/* Contains the last API call. */
		public $url;
		/* Set up the API root URL. */
		public $host;
		/* Set timeout default. */
		public $timeout = 30;
		/* Set connect timeout. */
		public $connecttimeout = 30;
		/* Verify SSL Cert. */
		public $ssl_verifypeer = false;
		/* Respons format. */
		public $format;
		/* Decode returned json data. */
		public $decode_json = true;
		/* Contains the last HTTP headers returned. */
		public $http_info;
		/* Set the useragnet. */
		public $useragent = 'Provider Oauth';
		/* Immediately retry the API call if the response was not successful. */
		//public $retry = TRUE;
		private $access_token_url;
		private $authenticate_token_url;
		private $authorize_url;
		private $request_token_url;
		private $consumer_key;
		private $consumer_secret;
		private $settings;
		private $max_count;
		private $default_count;

		private $popup_width;
		private $popup_height;


		/**
		 * Set API URLS
		 */
		function accessTokenURL() {
			return $this->access_token_url;
		}

		function authenticateURL() {
			return $this->authorize_url;
		}

		function authorizeURL() {
			return $this->authorize_url;
		}

		function requestTokenURL() {
			return $this->request_token_url;
		}

		/**
		 * Debug helpers
		 */
		function lastStatusCode() {
			return $this->http_status;
		}

		function lastAPICall() {
			return $this->last_api_call;
		}

		/**
		 * construct Provider oAuth object
		 */
		function __construct(
			$host,
			$format,
			$access_token_url,
			$authenticate_token_url,
			$authorize_url,
			$request_token_url,
			$consumer_key,
			$consumer_secret,
			$settings,
			$max_count,
			$default_count,
			$popup_width = 500,
			$popup_height = 500,
			$oauth_token = null,
			$oauth_token_secret = null
		) {

			$this->host                   = $host;
			$this->format                 = $format;
			$this->access_token_url       = $access_token_url;
			$this->authenticate_token_url = $authenticate_token_url;
			$this->authorize_url          = $authorize_url;
			$this->request_token_url      = $request_token_url;
			$this->consumer_key           = $consumer_key;
			$this->consumer_secret        = $consumer_secret;
			$this->settings               = $settings;
			$this->max_count              = $max_count;
			$this->popup_width            = $popup_width;
			$this->popup_height           = $popup_height;

			$this->sha1_method = new mmpOAuthSignatureMethod_HMAC_SHA1();
			$this->consumer    = new mmpOAuthConsumer( $this->consumer_key, $this->consumer_secret );
			//print 'oauth_token:' . $oauth_token;
			//print 'oauth_token_secret:' . $oauth_token_secret;
			if ( ! empty( $oauth_token ) && ! empty( $oauth_token_secret ) ) {
				$this->token = new mmpOAuthConsumer( $oauth_token, $oauth_token_secret );
			} else {
				$this->token = null;
			}
		}

		function get_popup_width() {
			return $this->popup_width;
		}

		function get_popup_height() {
			return $this->popup_height;
		}

		function get_authorise_url( $callback = '', $source = '' ) {

			$request_token = $this->getRequestToken( $callback . '&type=' . $source );

			//if ($request_token) return '#';
			if ( ! isset( $request_token['oauth_token'] ) && ! isset( $request_token['oauth_token_secret'] ) ) {
				return '';
			}

			$_SESSION[$source . '_oauth_token']        = $token = $request_token['oauth_token'];
			$_SESSION[$source . '_oauth_token_secret'] = $request_token['oauth_token_secret'];

			$url = '#';
			switch ( $this->http_code ) {
				case 200:
					$url = $this->getAuthorizeURL( $token );

					return $url;
					break;
			}

		}

		/**
		 * Get a request_token from Provider
		 *
		 * @returns a key/value array containing oauth_token and oauth_token_secret
		 */
		function getRequestToken( $oauth_callback = null ) {
			$parameters = array();
			if ( ! empty( $oauth_callback ) ) {
				$parameters['oauth_callback'] = $oauth_callback;
			}

			$request = $this->oAuthRequest( $this->requestTokenURL(), 'GET', $parameters );

			$token = mmpOAuthUtil::parse_parameters( $request );

			if ( ! isset( $token['oauth_token'] ) && ! isset( $token['oauth_token_secret'] ) ) {
				return array();
			}
			$this->token = new mmpOAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );

			return $token;
		}

		/**
		 * Get the authorize URL
		 *
		 * @returns a string
		 */
		function getAuthorizeURL( $token, $sign_in_with_twitter = true ) {
			if ( is_array( $token ) ) {
				$token = $token['oauth_token'];
			}
			if ( empty( $sign_in_with_twitter ) ) {
				return $this->authorizeURL() . "?oauth_token={$token}";
			} else {
				return $this->authenticateURL() . "?oauth_token={$token}";
			}
		}

		/**
		 * Exchange request token and secret for an access token and
		 * secret, to sign API calls.
		 *
		 */
		function getAccessToken( $oauth_verifier = false, $return_uri = null ) {
			$parameters = array();
			if ( ! empty( $oauth_verifier ) ) {
				$parameters['oauth_verifier'] = $oauth_verifier;
			}
			$request     = $this->oAuthRequest( $this->accessTokenURL(), 'GET', $parameters );
			$token       = mmpOAuthUtil::parse_parameters( $request );
			$this->token = new mmpOAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );

			return $token;
		}

		/**
		 * One time exchange of username and password for access token and secret.
		 *
		 */
		function getXAuthToken( $username, $password ) {
			$parameters                    = array();
			$parameters['x_auth_username'] = $username;
			$parameters['x_auth_password'] = $password;
			$parameters['x_auth_mode']     = 'client_auth';
			$request                       = $this->oAuthRequest( $this->accessTokenURL(), 'POST', $parameters );
			$token                         = mmpOAuthUtil::parse_parameters( $request );
			$this->token                   = new mmpOAuthConsumer( $token['oauth_token'], $token['oauth_token_secret'] );

			return $token;
		}

		/**
		 * GET wrapper for oAuthRequest.
		 */
		function get( $url, $parameters = array(), $mode = 0 ) {
			if ( $mode == 1 ) {
				$response = $this->oAuthRequest2( $url, 'GET', $parameters );
				$response = json_decode( json_encode( json_decode( $response ) ) );
			} else {
				if ( $mode == 2 ) {
					$response = $this->oAuthRequest3( $url, 'GET', $parameters );
					$response = json_decode( json_encode( json_decode( $response ) ) );
				} else {
					if ( $mode == 3 ) {
						$response = $this->oAuthRequestxml( $url, 'GET', $parameters );

						return $response;
						//$response = json_decode(json_encode(json_decode($response)));
					} else {
						$response = $this->oAuthRequest( $url, 'GET', $parameters );
					}
				}
			}
			if ( $this->format === 'json' && $this->decode_json && $response ) {
				return json_decode( $response );
			}

			return $response;
		}

		/**
		 * POST wrapper for oAuthRequest.
		 */
		function post( $url, $parameters = array() ) {
			$response = $this->oAuthRequest( $url, 'POST', $parameters );
			if ( $this->format === 'json' && $this->decode_json ) {
				return json_decode( $response );
			}

			return $response;
		}

		/**
		 * DELETE wrapper for oAuthReqeust.
		 */
		function delete( $url, $parameters = array() ) {
			$response = $this->oAuthRequest( $url, 'DELETE', $parameters );
			if ( $this->format === 'json' && $this->decode_json ) {
				return json_decode( $response );
			}

			return $response;
		}

		function getFormat( $url ) {
			return "{$this->host}{$url}.{$this->format}";
		}

		/**
		 * Format and sign an OAuth / API request
		 */
		function oAuthRequest( $url, $method, $parameters ) {
			if ( strrpos( $url, 'https://' ) !== 0 && strrpos( $url, 'http://' ) !== 0 ) {
				$url = $this->getFormat( $url );
			}
			$request = mmpOAuthRequest::from_consumer_and_token( $this->consumer, $this->token, $method, $url, $parameters );
			$request->sign_request( $this->sha1_method, $this->consumer, $this->token );

			switch ( $method ) {
				case 'GET':
					return $this->http( $request->to_url(), 'GET' );
				default:
					return $this->http( $request->get_normalized_http_url(), $method, $request->to_postdata() );
			}
		}


		/**
		 * Format and sign an OAuth / API request
		 */
		function oAuthRequest2( $url, $method, $parameters ) {
			if ( strrpos( $url, 'https://' ) !== 0 && strrpos( $url, 'http://' ) !== 0 ) {
				$url = $this->getFormat( $url );
			}
			$defaults                 = array();
			$token                    = $this->token;
			$defaults['access_token'] = $token->key;
			$parameters               = array_merge( $defaults, $parameters );
			$request                  = new mmpOAuthRequest( $method, $url, $parameters );

			switch ( $method ) {
				case 'GET':
					return $this->http( $request->to_url(), 'GET' );
				default:
					return $this->http( $request->get_normalized_http_url(), $method, $request->to_postdata() );
			}
		}

		function oAuthRequest3( $url, $method, $parameters ) {
			if ( strrpos( $url, 'https://' ) !== 0 && strrpos( $url, 'http://' ) !== 0 ) {
				$url = $this->getFormat( $url );
			}
			$request = new mmpOAuthRequest( $method, $url, $parameters );
			//_log($request->to_url());
			switch ( $method ) {
				case 'GET':
					return $this->http( $request->to_url(), 'GET' );
				default:
					return $this->http( $request->get_normalized_http_url(), $method, $request->to_postdata() );
			}
		}

		function oAuthRequestxml( $url, $method, $parameters ) {
			if ( strrpos( $url, 'https://' ) !== 0 && strrpos( $url, 'http://' ) !== 0 ) {
				$url = $this->getFormat( $url );
			}
			$request = new mmpOAuthRequest( $method, $url, $parameters );

			$result   = wp_remote_get( $request->to_url() );
			$response = '';
			if ( 200 == $result['response']['code'] && ! is_wp_error( $result ) ) {
				$response = $result['body'];
			}

			return $response;
		}


		/**
		 * Make an HTTP request
		 *
		 * @return API results
		 */
		function http( $url, $method, $postfields = null ) {
			$this->http_info = array();
			$ci              = curl_init();
			/* Curl settings */
			curl_setopt( $ci, CURLOPT_USERAGENT, $this->useragent );
			curl_setopt( $ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout );
			curl_setopt( $ci, CURLOPT_TIMEOUT, $this->timeout );
			curl_setopt( $ci, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ci, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
			curl_setopt( $ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer );
			curl_setopt( $ci, CURLOPT_HEADERFUNCTION, array( $this, 'getHeader' ) );
			curl_setopt( $ci, CURLOPT_HEADER, false );

			switch ( $method ) {
				case 'POST':
					curl_setopt( $ci, CURLOPT_POST, true );
					if ( ! empty( $postfields ) ) {
						curl_setopt( $ci, CURLOPT_POSTFIELDS, $postfields );
					}
					break;
				case 'DELETE':
					curl_setopt( $ci, CURLOPT_CUSTOMREQUEST, 'DELETE' );
					if ( ! empty( $postfields ) ) {
						$url = "{$url}?{$postfields}";
					}
			}
			curl_setopt( $ci, CURLOPT_URL, $url );
			$response = curl_exec( $ci );

			$this->http_code = curl_getinfo( $ci, CURLINFO_HTTP_CODE );
			$this->http_info = array_merge( $this->http_info, curl_getinfo( $ci ) );
			$this->url       = $url;
			curl_close( $ci );

			return $response;
		}

		/**
		 * Get the header info to store.
		 */
		function getHeader( $ch, $header ) {
			$i = strpos( $header, ':' );
			if ( ! empty( $i ) ) {
				$key                     = str_replace( '-', '_', strtolower( substr( $header, 0, $i ) ) );
				$value                   = trim( substr( $header, $i + 2 ) );
				$this->http_header[$key] = $value;
			}

			return strlen( $header );
		}

		/**
		 * filter text
		 */
		function filter_text( $text ) {
			return trim( filter_var( $text, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW ) );
		}

		public function show_details() {
			return $this->settings;
		}


		function get_param_choices( $type = '' ) {
		}
	}
}