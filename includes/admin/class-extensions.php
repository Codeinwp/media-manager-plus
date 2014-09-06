<?php

class Media_Manager_Plus_Extensions {

	/**
	 * Get the installed extension
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array of insstalled extensions
	 */
	public function get_installed_extensions() {
		$extensions = array();
		$plugins    = wp_get_active_and_valid_plugins();
		foreach ( $plugins as $key => $plugin ) {
			$pos = strpos( $plugin, 'media-manager-plus' );
			if ( $pos !== false ) {
				$extensions[] = basename( $plugin, ".php" );
			}
		}

		return $extensions;
	} // END get_installed_extensions()

	/**
	 * Render the extensions display
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_extensions() {
		$html = '';
		$extensions = $this->available_extensions();
		if ( count( $extensions ) > 0 ) {
			$html .= '<div id="uber-media-extensions">';
			$html .= '<ul>';
			foreach ( $extensions as $extension_data ) {
				$image_src = media_manager_plus()->get_value('extensions_base') . strtolower( $extension_data->name ) . '.png';
				$html .= '<li>';
				$html .= '<h3><a href="' . $extension_data->link . '" target="_blank">' . $extension_data->name . '</a></h3>';
				$html .= '<a href="' . $extension_data->link . '" target="_blank"><img src="' . $image_src . '" alt="' . $extension_data->name . ' logo"></a>';
				$html .= '<p>' . $extension_data->description . '</p>';
				if ( version_compare( media_manager_plus()->get_value('version'), $extension_data->requires, '<' ) ) {
					$html .= '<p><strong>'. sprintf( __( 'Requires Version %s', 'media-manager-plus' ), $extension_data->requires ) .'</strong></p>';
				}
				$html .= '<a target="_blank" class="button button-primary" title="' . sprintf( __( 'Buy the %s extension for $%s', 'media-manager-plus' ), $extension_data->name, $extension_data->price ) . '" href="' . $extension_data->link . '">' . sprintf( __( '$%s Buy', 'media-manager-plus' ), $extension_data->price ) . '</a>';
				$html .= '</li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		} else {
			$html = __( 'No new extensions available, you must have installed them all. Nice.', 'media-manager-plus' );
		}

		echo $html;
	} // END get_extensions

	/**
	 * Retrieve extensions from external json file or site transient
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array|mixed of available extensions
	 */
	public function available_extensions() {
		if ( false === ( $available_extensions = get_transient( 'mmp_available_extensions' ) ) ) {
			$result     = wp_remote_get( media_manager_plus()->get_value('extensions_url') );
			$extensions = array();
			$available_extensions = array();
			if ( 200 == $result['response']['code'] ) {
				$json       = json_decode( $result['body'] );
				$extensions = $json->extensions;
			}
			if ( $extensions ) {
				foreach ( $extensions as $extension_data ) {
					$plugin_name     = $extension_data->pluginFile;
					$validate_plugin = validate_plugin( $plugin_name );
					if ( ! is_wp_error( $validate_plugin ) && $validate_plugin == 0 ) {
						continue;
					}
					$available_extensions[] = $extension_data;
				}
				// If Bundle is the obnly extension not installed remove from extension array
				if ( count ( $available_extensions ) == 1 && $available_extensions[0]->name == 'Bundle' ) {
					unset( $available_extensions[0] );
				}
			}
			set_transient( 'mmp_available_extensions', $available_extensions, 12 * HOUR_IN_SECONDS );
		}

		return $available_extensions;
	} // END available_extensions

} // END Media_Manager_Plus_Extensions