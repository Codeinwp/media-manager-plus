<?php

class Media_Manager_Plus_Install {

	function __construct() {
		register_activation_hook( Media_Manager_Plus::get_value('plugin_file'), array( $this, 'activate' ) );
		add_action( 'admin_init', array( $this, 'upgrade_check' ) );

		if ( apply_filters( 'mmp_welcome', true ) ) {
			add_action( 'admin_init', array( $this, 'welcome' ) );
		}
	} // END __construct()

	/**
	 * Fired when plugin is activated
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $network_wide TRUE if WPMU 'super admin' uses Network Activate option
	 * @return void
	 */
	public function activate( $network_wide ) {
		set_transient( '_mmp_activation_redirect', true, 30 );
	} // END activate()

	/**
	 * Upgrade checker
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function upgrade_check() {
		$version = Media_Manager_Plus::get_value('version');
		if ( ! get_option( 'mmp_version' ) ) {
			add_option( 'mmp_version', $version );

			return;
		}
		$current_version = get_option( 'mmp_version' );
		if ( version_compare( $current_version, $version, '!=' ) ) {
			// Large upgrade to v1.2
			if ( version_compare( $current_version, '1.2', '<' ) ) {
				set_transient( '_mmp_activation_redirect', true, 30 );
			}
			update_option( 'mmp_version', $version );
		}
	} // END upgrade_check()

	/**
	 * Display Welcome screen if needed
	 *
	 * @since 1.0.0
	 * @access public
	 */
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
	} // END welcome()

} // END Media_Manager_Plus_Install

new Media_Manager_Plus_Install;