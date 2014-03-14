<?php
/**
 * Flickr
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

function add_uber_media_flickr( $sources ) {
	$sources['flickr'] = array(
		'source' => 'flickr',
		'core'   => true
	);

	return $sources;
}

add_filter( 'uber_media_sources', 'add_uber_media_flickr', 30 );

function flickr_mmp_settings( $wpsf_ubermedia_settings ) {
	$flickr   = new media_manager_plus_source_flickr();
	$licenses = $flickr->getLicenses();
	$choices  = array();
	foreach ( $licenses as $license ) {
		$choices[$license->id] = $license->name;
	}
	$fields   = $wpsf_ubermedia_settings[2]['fields'];
	$fields[] = array(
		'id'      => 'flickr-license',
		'title'   => __( 'Flickr License Options' ),
		'desc'    => __( 'Select the license types of images returned from Flickr' ),
		'type'    => 'checkboxes',
		'choices' => $choices,
		'std'     => ''
	);

	$wpsf_ubermedia_settings[2]['fields'] = $fields;

	return $wpsf_ubermedia_settings;
}

add_filter( 'uber_media_settings', 'flickr_mmp_settings' );

class media_manager_plus_source_flickr extends media_manager_plus_source {

	public $host = 'http://api.flickr.com/services/rest/';
	public $format = 'json';
	private $access_token_url = 'http://www.flickr.com/services/oauth/access_token';
	private $authenticate_token_url = 'http://www.flickr.com/services/oauth/authorize';
	private $authorize_url = 'http://www.flickr.com/services/oauth/authorize';
	private $request_token_url = 'http://www.flickr.com/services/oauth/request_token';

	private $consumer_key = 'd95b51541ae40a9950bea98e24a27cfd';
	private $consumer_secret = 'ae1b30477cc1bdfb';

	private $max_count = 500;
	private $default_count = 20;

	private $popup_width = 700;
	private $popup_height = 600;

	private $settings = array(
		'getTaggedImages' => array(
			'name'       => 'Tagged Images',
			'param'      => true,
			'param_type' => 'text',
			'param_desc' => 'Enter a hashtag without the #'
		),
		'getUsersImages'  => array(
			'name'       => 'User Images',
			'param'      => true,
			'param_type' => 'text',
			'param_desc' => 'Enter a username'
		),
		'getRecent'       => array(
			'name'  => 'Recent Images (All Licenses)',
			'param' => false
		),
	);

	function __construct( $oauth_token = null, $oauth_token_secret = null ) {

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

	function getFormat( $url ) {
		return "{$this->host}{$url}?format={$this->format}&nojsoncallback=1";
	}

	private function getImages( $images, $page ) {
		$response   = array();
		$new_images = array();
		if ( $images && isset( $images->photos->photo ) ) {
			if ( $page == $images->photos->pages ) {
				$response['pagin'] = false;
			}
			foreach ( $images->photos->photo as $photo ) {
				$new_images[] = array(
					'id'        => $photo->id,
					'full'      => 'http://farm' . $photo->farm . '.static.flickr.com/' . $photo->server . '/' . $photo->id . '_' . $photo->secret . '_b.jpg',
					'thumbnail' => 'http://farm' . $photo->farm . '.static.flickr.com/' . $photo->server . '/' . $photo->id . '_' . $photo->secret . '_q.jpg',
					'link'      => 'http://www.flickr.com/photos/' . $photo->owner . '/' . $photo->id,
					'caption'   => ( isset( $photo->title ) ? $this->filter_text( $photo->title ) : '' )
				);
			}
		}
		$response['images'] = $new_images;

		return $response;
	}

	function getLicenses() {
		$params   = array(
			'method'  => 'flickr.photos.licenses.getInfo',
			'api_key' => $this->consumer_key
		);
		$licenses = $this->get( '', $params );

		return isset( $licenses->licenses->license ) ? $licenses->licenses->license : array();
	}

	private function addLicenseParam( $params ) {
		$mmp_options = get_option( 'ubermediasettings_settings', array() );
		if ( $mmp_options ) {
			$value    = 'ubermediasettings_general_flickr-license';
			$licenses = ( ! isset( $mmp_options[$value] ) ) ? array() : $mmp_options[$value];
			if ( count( $licenses ) > 0 ) {
				$license_string    = implode( ',', $licenses );
				$params['license'] = $license_string;
			}
		}

		return $params;
	}

	private function getUserId( $username ) {
		$params = array(
			'method'   => 'flickr.people.findByUsername',
			'username' => $username
		);
		$userid = 0;
		$user   = $this->get( '', $params );
		if ( isset( $user->user ) && isset( $user ) ) {
			$user   = $user->user;
			$userid = $user->id;
		}

		return $userid;

	}

	function getOwnImages( $count = null, $safemode = 1, $page = 1 ) {
		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params = array(
			'method'   => 'flickr.photos.search',
			'user_id'  => 0,
			'per_page' => $count,
			'page'     => $page
		);
		if ( $safemode == 1 ) {
			$params['safe_search'] = 1;
		}
		$images = array();
		$params = $this->addLicenseParam( $params );
		if ( $userid != 0 ) {
			$images = $this->get( '', $params );
		}

		return $this->getImages( $images, $page );
	}

	function getUsersImages( $username, $count = null, $safemode = 1, $page = 1 ) {
		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$userid = $this->getUserId( $username );
		$params = array(
			'method'   => 'flickr.photos.search',
			'user_id'  => $userid,
			'per_page' => $count,
			'page'     => $page
		);
		if ( $safemode == 1 ) {
			$params['safe_search'] = 1;
		}
		$images = array();
		$params = $this->addLicenseParam( $params );
		if ( $userid != 0 ) {
			$images = $this->get( '', $params );
		}

		return $this->getImages( $images, $page );
	}

	function getTaggedImages( $tags, $count = null, $safemode = 1, $page = 1 ) {

		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params = array(
			'method'   => 'flickr.photos.search',
			'tags'     => $tags,
			'tag_mode' => 'all',
			'per_page' => $count,
			'page'     => $page
		);
		if ( $safemode == 1 ) {
			$params['safe_search'] = 1;
		}
		$images = array();
		$params = $this->addLicenseParam( $params );
		$images = $this->get( '', $params );

		return $this->getImages( $images, $page );
	}

	function getRecent( $count = null, $safemode = 1, $page = 1 ) {
		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params = array(
			'method'   => 'flickr.photos.getRecent',
			'per_page' => $count,
			'page'     => $page
		);
		if ( $safemode == 1 ) {
			$params['safe_search'] = 1;
		}
		$images = array();
		$images = $this->get( '', $params );

		return $this->getImages( $images, $page );
	}


}
