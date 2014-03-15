<?php
/**
 * 500px
 *
 * Media Manager Plus Image Source
 *
 * DISCLAIMER
 *
 * Do not edit or add directly to this file if you wish to upgrade Media Manager Plus to newer
 * versions in the future.
 *
 * @package             Media Manager Plus
 * @category            Image Source
 * @author              Dev7studios
 *
 **/

function add_uber_media_500px( $sources ) {
	$sources['500px'] = array(
		'source' => '500px',
		'core'   => true
	);

	return $sources;
}

add_filter( 'uber_media_sources', 'add_uber_media_500px', 10 );

function mmp_settings_500px( $wpsf_ubermedia_settings ) {
	$choices  = array(
		'-1' => __( 'Any License', 'media-manager-plus' ),
		'0'  => 'Standard 500px License',
		'1'  => 'Attribution-NonCommercial 3.0',
		'2'  => 'Attribution-NonCommercial-NoDerivs 3.0',
		'3'  => 'Attribution-NonCommercial-ShareAlike 3.0',
		'4'  => 'Attribution 3.0',
		'5'  => 'Attribution-NoDerivs 3.0',
		'6'  => 'Attribution-ShareAlike 3.0'
	);
	$fields   = $wpsf_ubermedia_settings['general']['fields'];
	$fields[] = array(
		'id'      => '500px-license',
		'title'   => __( '500px License Options' ),
		'desc'    => __( 'Select the license type of images returned from 500px' ),
		'type'    => 'select',
		'choices' => $choices,
		'std'     => '-1'
	);

	$wpsf_ubermedia_settings['general']['fields'] = $fields;

	return $wpsf_ubermedia_settings;
}

add_filter( 'uber_media_settings', 'mmp_settings_500px' );

class media_manager_plus_source_500px extends media_manager_plus_source {

	public $host = 'https://api.500px.com/v1/';
	public $format = 'json';
	private $access_token_url = 'https://api.500px.com/v1/oauth/access_token';
	private $authenticate_token_url = 'https://api.500px.com/v1/oauth/authorize';
	private $authorize_url = 'https://api.500px.com/v1/oauth/authorize';
	private $request_token_url = 'https://api.500px.com/v1/oauth/request_token';

	private $consumer_key = 'sjQOB4EmdL7zg6BZK5XIhxhSVrC2y82pz1eBbzk6';
	private $consumer_secret = 'VtMOtA0Keirpo7oOTftJcq88uKLLLN2RfxV2X7Xp';

	private $max_count = 100;
	private $default_count = 20;

	private $popup_width = 800;
	private $popup_height = 500;

	private $settings = array();

	function __construct( $oauth_token = null, $oauth_token_secret = null ) {

		$this->settings = array(
			'getTaggedImages' => array(
				'name'			=> __( 'Tagged Images', 'media-manager-plus' ),
				'param'			=> true,
				'param_type'	=> 'text',
				'param_desc'	=> __( 'Enter a hashtag without the #', 'media-manager-plus' ),
			),
			'getUsersImages' => array(
				'name'			=> __( 'User Images', 'media-manager-plus' ),
				'param'			=> true,
				'param_type'	=> 'text',
				'param_desc'	=> __( 'Enter a username', 'media-manager-plus' ),
			),
			'getPopular' => array(
				'name'	=> __( 'Popular Images', 'media-manager-plus' ),
				'param'	=> false,
			),
		);

		parent::__construct(
			  $this->host,
				  $this->format,
				  $this->access_token_url,
				  $this->authenticate_token_url,
				  $this->authorize_url,
				  $this->request_token_url,
				  $this->consumer_key,
				  $this->consumer_secret,
				  $this->settings,
				  $this->max_count,
				  $this->default_count,
				  $this->popup_width,
				  $this->popup_height,
				  $oauth_token,
				  $oauth_token_secret
		);

	}

	private function addLicenseParam( $params ) {
		$mmp_options = get_option( 'ubermediasettings_settings', array() );
		if ( $mmp_options ) {
			$value   = 'ubermediasettings_general_500px-license';
			$license = ( ! isset( $mmp_options[$value] ) ) ? '' : $mmp_options[$value];
			if ( $license != '-1' ) {
				$params['license_type'] = $license;
			}
		}

		return $params;
	}

	private function getImages( $images, $page ) {
		$response   = array();
		$new_images = array();
		if ( $images && isset( $images->photos ) ) {
			if ( $page == $images->total_pages ) {
				$response['pagin'] = false;
			}
			foreach ( $images->photos as $photo ) {
				$new_images[] = array(
					'id'        => $photo->id,
					'full'      => str_replace( '/2.jpg', '/4.jpg', $photo->image_url ),
					'thumbnail' => $photo->image_url,
					'link'      => 'http://500px.com/photo/' . $photo->id,
					'caption'   => ( isset( $photo->name ) ? $this->filter_text( $photo->name ) : '' )
				);
			}
		}
		$response['images'] = $new_images;

		return $response;
	}

	function getUsersImages( $username, $count = null, $safemode = 1, $page = 1 ) {
		$count          = isset( $count ) ? $count : $this->default_count;
		$count          = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params         = array( 'feature' => 'user', 'username' => $username );
		$params['rpp']  = $count;
		$params['page'] = $page;
		if ( $safemode == 1 ) {
			$params['exclude'] = 'Nude';
		}
		$params = $this->addLicenseParam( $params );
		$images = $this->get( 'photos', $params );

		return $this->getImages( $images, $page );
	}

	function getTaggedImages( $tag, $count = null, $safemode = 1, $page = 1 ) {
		$count          = isset( $count ) ? $count : $this->default_count;
		$count          = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params         = array( 'tag' => $tag );
		$params['rpp']  = $count;
		$params['page'] = $page;
		if ( $safemode == 1 ) {
			$params['exclude'] = 'Nude';
		}
		$params = $this->addLicenseParam( $params );
		$images = $this->get( 'photos/search', $params );

		return $this->getImages( $images, $page );
	}

	function getPopular( $count = null, $safemode = 1, $page = 1 ) {
		$count          = isset( $count ) ? $count : $this->default_count;
		$count          = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params         = array( 'feature' => 'popular' );
		$params['rpp']  = $count;
		$params['page'] = $page;
		if ( $safemode == 1 ) {
			$params['exclude'] = 'Nude';
		}
		$params = $this->addLicenseParam( $params );
		$images = $this->get( 'photos', $params );

		return $this->getImages( $images, $page );
	}

}
