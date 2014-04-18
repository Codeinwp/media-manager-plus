<?php

class Media_Manager_Plus_Settings {

	/**
	 * @var array
	 */
	public $settings;

	/**
	 * @var mmpWordPressSettingsFramework
	 */
	public $wpsf;

	function __construct() {
		require_once MMP_PLUGIN_DIR . 'includes/settings/wp-settings-framework.php';
		$this->wpsf = new mmpWordPressSettingsFramework( MMP_PLUGIN_DIR . 'includes/settings/uber-media-settings.php', '' );
		add_filter( $this->wpsf->get_option_group() . '_settings_validate', array( $this, 'validate_settings' ) );
		$this->settings = wpsf_get_settings( MMP_PLUGIN_DIR . 'includes/settings/uber-media-settings.php' );
	} // END __construct()

	/**
	 * Process the settings section for display
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param $page
	 */
	public function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'sources';
		if ( ! isset( $wp_settings_sections ) || ! isset( $wp_settings_sections[$page] ) ) {
			return;
		}
		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			echo '<div id="section-' . $section['id'] . '"class="ubermedia-section' . ( $active_tab == $section['id'] ? ' ubermedia-section-active' : '' ) . '">';

			if ( $section['id'] == 'sources' ) {
				$this->setting_image_sources();
			} else {
				if ( $section['id'] == 'extensions' ) {
					media_manager_plus()->extensions->get_extensions();
				} else {
					call_user_func( $section['callback'], $section );
					if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[$page] ) || ! isset( $wp_settings_fields[$page][$section['id']] ) ) {
						continue;
					}
					echo '<table class="form-table">';
					echo '<input type="hidden" name="ubermediasettings_settings[sources]" value="sources">';
					do_settings_fields( $page, $section['id'] );
					echo '</table>';
					if ( $section['id'] != 'support' ) {
						submit_button();
					}
				}
			}
			echo '</div>';
		}
	} // END do_settings_sections()

	/**
	 * Validate settings on save
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param $input
	 *
	 * @return mixed
	 */
	function validate_settings( $input ) {
		if ( isset( $input['sources'] ) ) {
			$sources = mmp_default_val( 'ubermediasettings_sources_available', array() );
			$input['ubermediasettings_sources_available'] = $sources;
			unset( $input['sources'] );
		}

		return $input;
	} // END validate_settings()

	/**
	 * Renders the image sources
	 *
	 * @since 1.0.0
	 * @access public
	 */
	function setting_image_sources() {
		$sources = media_manager_plus()->sources->get_sources();
		$html    = '';
		if ( $sources ) {
			$html .= '<div id="uber-media-settings">';
			$html .= '<iframe id="logoutframe" src="https://instagram.com/accounts/logout/" width="0" height="0"></iframe>';
			$html .= '<ul>';
			foreach ( $sources as $source => $source_data ) {
				$class    = ( $source_data['url'] == '#' ) ? 'disconnect' : 'connect';
				if ( $class == 'connect' ) {
					$text = __( 'Connect', 'media-manager-plus' );
				} else {
					$text = __( 'Disconnect', 'media-manager-plus' );
				}
				$title = $text . ' ' . $source_data['name'];
				$disabled = '';
				$btnclass = ( $source_data['url'] == '#' ) ? '' : ' button-primary';
				if ( $source_data['url'] == '' ) {
					$text     = __( 'API Unavailable', 'media-manager-plus' );
					$class    = 'disconnect';
					$disabled = 'disabled="disabled" ';
					$btnclass = '';
					$title    = $source_data['name'] . ' '. __( 'API Unavailable', 'media-manager-plus' );
				}

				$width  = ( isset( $source_data['w'] ) ) ? 'data-w="' . $source_data['w'] . '" ' : '';
				$height = ( isset( $source_data['h'] ) ) ? 'data-h="' . $source_data['h'] . '" ' : '';
				$html .= '<li>';
				$html .= '<img src="' . $source_data['imgsrc'] . '" alt="' . $source_data['name'] . ' logo">';
				$html .= '<a ' . $disabled . 'data-source="' . $source . '" ' . $width . $height . 'class="button uber-connect ' . $class . $btnclass . '" title="' . $title . '" href="' . $source_data['url'] . '">' . $text . '</a></li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}
		if ( $html == '' ) {
			$html = __( 'No available sources', 'media-manager-plus' );
		}
		echo $html;
	} // END setting_image_sources()

} // END Media_Manager_Plus_Settings