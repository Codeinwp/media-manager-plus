<?php

class Media_Manager_Plus_Admin {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( MMP_PLUGIN_FILE ), array( $this, 'add_action_links' ) );
	} // END __construct()

	/**
	 * Register and enqueue admin-specific assets.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $hook Current page hook
	 */
	public function admin_enqueue_scripts( $hook ) {

		$pages       = array( 'dashboard_page_mmp-welcome', 'media_page_mmp', 'post.php', 'post-new.php' );
		$admin_pages = apply_filters( 'uber_media_enqueue_pages', $pages );
		$dev         = apply_filters( 'uber_media_debug_mode', SCRIPT_DEBUG ) ? '' : '.min';

		if ( in_array( $hook, $admin_pages ) ) {

			wp_enqueue_media();
			wp_register_script( 'uber-media-js', MMP_PLUGIN_URL . "assets/js/uber-media{$dev}.js", array( 'media-views' ), MMP_VERSION );
			wp_enqueue_script( 'uber-media-js' );

			wp_localize_script(
				'uber-media-js',
				'uber_media',
				array(
					'nonce' => 	wp_create_nonce( 'uber_media' )
				)
			);

			wp_register_style( 'uber-media-css', MMP_PLUGIN_URL . "assets/css/uber-media{$dev}.css", array(), MMP_VERSION );
			wp_enqueue_style( 'uber-media-css' );

		} // END if

	} // END admin_enqueue_scripts()

	/**
	 * Adds the MMP menu under the media top level page
	 * Adds the welcome screen sub pages
	 *
	 * @since 1.0.0
	 * @access public
	 */
	function admin_menu() {
		add_media_page(
			__( 'Media Manager Plus', 'media-manager-plus' ),
			__( 'Media Manager Plus', 'media-manager-plus' ),
			'read',
			'mmp',
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
	} // END admin_menu()

	/**
	 * Removes the welcome sub pages from the menus
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'mmp-welcome' );
		remove_submenu_page( 'index.php', 'mmp-support' );
	} // END admin_head()

	/**
	 * Renders the MMP settings page
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}
		media_manager_plus()->templates->get_template_part( 'settings' );
	} // END settings_page()

	/**
	 * Renders the MMP welcome page
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function welcome_screen() {
		media_manager_plus()->templates->get_template_part( 'welcome' );
	} // END welcome()

	/**
	 * Renders the MMP welcome support page
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function support_screen() {
		media_manager_plus()->templates->get_template_part( 'support' );
	} // END support_screen()

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
				'settings' => '<a href="' . add_query_arg( array( 'page' => 'mmp' ), admin_url( 'upload.php' ) ) . '">' . __( 'Settings' ) . '</a>'
			),
			$links
		);

	} // END add_action_links()

} // END Media_Manager_Plus_Admin

new Media_Manager_Plus_Admin;
