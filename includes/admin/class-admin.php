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

		$pages       = array( 'dashboard_page_mmp-welcome', 'media_page_uber-media', 'post.php', 'post-new.php' );
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
		global $wpsf_ubermedia_settings;
		$wpsf_ubermedia_settings = apply_filters( 'uber_media_settings', $wpsf_ubermedia_settings );
		$active_tab              = isset( $_GET['tab'] ) ? $_GET['tab'] : 'sources';
		?>
		<div class="wrap">
			<div id="icon-upload" class="icon32"></div>
			<h2>
				<?php _e( 'Media Manager Plus', 'media-manager-plus' ); ?>
				<?php echo apply_filters( 'uber_media_title_version', "<span class='uber-version'>v ". MMP_VERSION . "</span>" ); ?>
			</h2>

			<h2 class="nav-tab-wrapper">
				<?php foreach ( $wpsf_ubermedia_settings as $tab ) {
					// Hide Extensions tab if none available
					$extensions = media_manager_plus()->extensions->available_extensions();
					if ( count( $extensions ) == 0 && $tab['section_id'] == 'extensions' ) {
						continue;
					}
					?>
					<a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $tab['section_id']; ?>" class="nav-tab<?php echo( $active_tab == $tab['section_id'] ? ' nav-tab-active' : '' ); ?>"><?php echo $tab['section_title']; ?></a>
				<?php } ?>
			</h2>

			<form action="options.php" method="post">
				<?php settings_fields( media_manager_plus()->settings->wpsf->get_option_group() ); ?>
				<?php media_manager_plus()->settings->do_settings_sections( media_manager_plus()->settings->wpsf->get_option_group() ); ?>
			</form>
		</div>
	<?php
	} // END settings_page()

	/**
	 * Renders the MMP welcome page
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function welcome_screen() {
		?>
		<div class="wrap about-wrap mmp-welcome">
			<h1><?php printf( __( 'Welcome to Media Manager Plus', 'media-manager-plus' ), MMP_VERSION ); ?></h1>

			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Media Manager Plus %s upgrades the WordPress media manager with third party image sources.', 'media-manager-plus' ), MMP_VERSION ); ?></div>

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
				<?php media_manager_plus()->extensions->get_extensions(); ?>
			</div>
			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'uber-media' ), 'upload.php' ) ) ); ?>"><?php _e( 'Go to Media Manager Plus Settings', 'media-manager-plus' ); ?></a>
			</div>
		</div>
	<?php
	} // END welcome()

	/**
	 * Renders the MMP welcome support page
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function support_screen() {
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Media Manager Plus', 'media-manager-plus' ), MMP_VERSION ); ?></h1>

			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Media Manager Plus %s upgrades the WordPress media manager with third party image sources.', 'media-manager-plus' ), MMP_VERSION ); ?></div>

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
				'settings' => '<a href="' . add_query_arg( array( 'page' => 'uber-media' ), admin_url( 'upload.php' ) ) . '">' . __( 'Settings', 'media-manager-plus' ) . '</a>'
			),
			$links
		);

	} // END add_action_links()

} // END Media_Manager_Plus_Admin

new Media_Manager_Plus_Admin;
