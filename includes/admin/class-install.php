<?php

class Media_Manager_Plus_Install {

	function __construct() {
		register_activation_hook( MMP_PLUGIN_FILE, array( $this, 'activate' ) );
		add_action( 'admin_init', array( $this, 'upgrade_check' ) );
		add_action( 'admin_init', array( $this, 'welcome' ) );
	}

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

	public function upgrade_check() {
		if ( ! get_option( 'mmp_version' ) ) {
			add_option( 'mmp_version', MMP_VERSION );

			return;
		}
		$current_version = get_option( 'mmp_version' );
		if ( version_compare( $current_version, MMP_VERSION, '!=' ) ) {
			// Large upgrade to v1.2
			if ( version_compare( $current_version, '1.2', '<' ) ) {
				set_transient( '_mmp_activation_redirect', true, 30 );
			}
			update_option( 'mmp_version', MMP_VERSION );
		}
	} // END upgrade_check()

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

}

new Media_Manager_Plus_Install;