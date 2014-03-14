<?php
if ( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( is_multisite() ) {

	global $wpdb;
	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );

	if ( $blogs ) {
		foreach( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			delete_option( 'ubermediasettings_settings' );
			delete_option( 'mmp_version' );
		}
		restore_current_blog();
	}

} else {

	delete_option( 'ubermediasettings_settings' );
	delete_option( 'mmp_version' );

}
