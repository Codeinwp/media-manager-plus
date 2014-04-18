<?php

class Media_Manager_Plus_Media {

	function __construct() {
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ), 99 );
		add_filter( 'media_view_strings', array( $this, 'custom_media_string' ), 10, 2 );
		add_action( 'wp_ajax_uber_pre_insert', array( $this, 'pre_insert' ) );
	} // END _construct()

	/**
	 * Hook before an image string gets inserted into the content editor used by extensions
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int
	 */
	public function pre_insert() {
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
	} // END pre_insert()

	/**
	 * Send custom strings to the media javascript files
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param $strings
	 * @param $post
	 *
	 * @return mixed
	 */
	public function custom_media_string( $strings, $post ) {
		$strings['mmp_sources']     = media_manager_plus()->sources->get_sources( true );
		$strings['mmp_menu']        = apply_filters( 'mmp_default_menu', 'default' );
		$strings['mmp_menu_prefix'] = apply_filters( 'mmp_menu_prefix', __( 'Insert from ', 'media-manager-plus' ) );
		$strings['mmp_defaults']    = apply_filters( 'mmp_default_settings', array() );
		$strings['mmp_extensions']  = media_manager_plus()->extensions->get_installed_extensions();
		$strings['mmp_l10n']		= $this->get_js_l10n( $post );

		return $strings;
	} // END custom_media_string()

	/**
	 * Render the media sidebar
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_media_templates() {
		media_manager_plus()->templates->get_template_part( 'media' );
	} // END print_media_templates()

	/**
	 * Gets the translatable strings for the javascript file
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_js_l10n( $post ){
		$hier = $post && is_post_type_hierarchical( $post->post_type );
		return array(
			'disconnect'	=>	__( 'Disconnect', 'media-manager-plus' ),
			'connect'		=> 	__( 'Connect', 'media-manager-plus' ),
			'connecting'	=>	__( 'Connecting', 'media-manager-plus' ),
			'importing'		=>	__( 'Importing', 'media-manager-plus' ),
			'inserting'		=>	__( 'Inserting', 'media-manager-plus' ),
			'insert'		=>	$hier ? __( 'Insert into page', 'media-manager-plus' ) : __( 'Insert into post', 'media-manager-plus' ),
			'imported'		=>	__( 'imported', 'media-manager-plus' ),
			'import'		=>	__( 'Import', 'media-manager-plus' ),
			'image'			=>	__( 'image', 'media-manager-plus' ),
			'images'		=>	__( 'images', 'media-manager-plus' )
		);
	} // END get_js_l10n()

} // END Media_Manager_Plus_Media

new Media_Manager_Plus_Media;