<?php

/**
 * Get the option or return a default
 *
 * @param        $value
 * @param string $default
 *
 * @return string
 */
function mmp_default_val( $value, $default = '' ) {
	$options = media_manager_plus()->settings->settings;
	if ( ! isset( $options[$value] ) ) {
		return $default;
	} else {
		return $options[$value];
	}
} // END mmp_default_val()