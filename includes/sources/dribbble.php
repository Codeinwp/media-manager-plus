<?php
/**
 * Dribbble
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

function add_uber_media_dribbble( $sources ) {
	$sources['dribbble'] = array(
		'source' => 'dribbble',
		'core'   => true
	);

	return $sources;
}

add_filter( 'uber_media_sources', 'add_uber_media_dribbble', 20 );

class media_manager_plus_source_dribbble extends media_manager_plus_source {

	public $host = 'http://api.dribbble.com/';
	public $format;
	private $access_token_url = '';
	private $authenticate_token_url = '';
	private $authorize_url = '';
	private $request_token_url = '';

	private $consumer_key = '';
	private $consumer_secret = '';
	private $redirect_uri = '';

	private $max_count = 50;
	private $default_count = 20;

	private $popup_width = 1;
	private $popup_height = 1;

	private $settings = array(
		'getPopular'      => array(
			'name'  => 'Popular Shots',
			'param' => false
		),
		'getEveryone'     => array(
			'name'  => "Everyone's Shots",
			'param' => false
		),
		'getDebuts'       => array(
			'name'  => "Debut Shots",
			'param' => false
		),
		'getPlayers'      => array(
			'name'       => "Player's Shots",
			'param'      => true,
			'param_type' => 'text',
			'param_desc' => "Enter a player's name"
		),
		'getPlayersLikes' => array(
			'name'       => "Player's Liked Shots",
			'param'      => true,
			'param_type' => 'text',
			'param_desc' => "Enter a player's name"
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
		return "{$this->host}{$url}";
	}

	function get_authorise_url( $callback = '', $source = '' ) {
		$_SESSION[$source . '_oauth_token']        = $source . '_token';
		$_SESSION[$source . '_oauth_token_secret'] = $source . '_secret';

		return $callback . '&type=' . $source . '&oauth_verifier=' . $source;
	}


	function getAccessToken( $oauth_verifier = false, $return_uri = null ) {
		$access_token['oauth_token']        = 'notoken';
		$access_token['oauth_token_secret'] = 'nosecret';

		return $access_token;
	}

	private function getImages( $images, $page ) {
		$response   = array();
		$new_images = array();
		if ( $images && isset( $images->shots ) ) {
			if ( $page == $images->pages ) {
				$response['pagin'] = false;
			}
			foreach ( $images->shots as $shot ) {
				$new_images[] = array(
					'id'        => $shot->id,
					'full'      => ( isset( $shot->image_400_url ) ? $shot->image_400_url : $shot->image_url ),
					'thumbnail' => $shot->image_teaser_url,
					'link'      => $shot->url,
					'caption'   => ( isset( $shot->title ) ? $this->filter_text( $shot->title ) : '' )
				);
			}
		}
		$response['images'] = $new_images;

		return $response;
	}

	function getPlayers( $username, $count = null, $safemode = 1, $page = 1 ) {
		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params = ( $count == $this->default_count ) ? array() : array( 'per_page' => $count, 'page' => $page );
		$images = $this->get( '/players/' . $username . '/shots', $params, 2 );

		return $this->getImages( $images, $page );
	}

	function getPlayersLikes( $username, $count = null, $safemode = 1, $page = 1 ) {
		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params = ( $count == $this->default_count ) ? array() : array( 'per_page' => $count, 'page' => $page );
		$images = $this->get( '/players/' . $username . '/shots/likes', $params, 2 );

		return $this->getImages( $images, $page );
	}

	function getPopular( $count = null, $safemode = 1, $page = 1 ) {
		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params = ( $count == $this->default_count ) ? array() : array( 'per_page' => $count, 'page' => $page );
		$images = $this->get( 'shots/popular', $params, 2 );

		return $this->getImages( $images, $page );
	}

	function getEveryone( $count = null, $safemode = 1, $page = 1 ) {
		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params = ( $count == $this->default_count ) ? array() : array( 'per_page' => $count, 'page' => $page );
		$images = $this->get( 'shots/everyone', $params, 2 );

		return $this->getImages( $images, $page );
	}

	function getDebuts( $count = null, $safemode = 1, $page = 1 ) {
		$count  = isset( $count ) ? $count : $this->default_count;
		$count  = ( $count > $this->max_count ) ? $this->max_count : $count;
		$params = ( $count == $this->default_count ) ? array() : array( 'per_page' => $count, 'page' => $page );
		$images = $this->get( 'shots/debuts', $params, 2 );

		return $this->getImages( $images, $page );
	}


}
